import React, { memo, useEffect, useRef, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faEthernet, faHdd, faMemory, faMicrochip, faServer } from '@fortawesome/free-solid-svg-icons';
import { Link } from 'react-router-dom';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerPowerState, ServerStats } from '@/api/server/getServerResourceUsage';
import { bytesToString, ip, mbToBytes } from '@/lib/formatters';
import tw from 'twin.macro';
import Spinner from '@/components/elements/Spinner';
import styled, { keyframes } from 'styled-components/macro';
import isEqual from 'react-fast-compare';
import useTranslation from '@/plugins/useTranslation';

// Determines if the current value is in an alarm threshold so we can show it in red rather
// than the more faded default style.
const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

const ledPulse = keyframes`
    0%, 100% { opacity: 1; }
    50% { opacity: 0.45; }
`;

const statusColor = ($status: ServerPowerState | undefined): string => {
    if (!$status || $status === 'offline') return '#f87171';
    if ($status === 'running') return '#4ade80';
    return '#facc15';
};

const Blade = styled(Link)<{ $status: ServerPowerState | undefined }>`
    ${tw`relative rounded-2xl p-6 no-underline overflow-hidden transition-all duration-300 block`};
    background:
        linear-gradient(165deg, rgb(var(--aurex-surface-2)) 0%, rgb(var(--aurex-surface)) 50%, rgb(var(--aurex-bg)) 100%) padding-box,
        linear-gradient(150deg, rgb(var(--aurex-300) / 0.5), rgb(var(--aurex-600) / 0.12) 45%, rgb(var(--aurex-border) / 0.4) 100%) border-box;
    border: 1px solid transparent;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);

    &::before {
        content: '';
        ${tw`absolute top-0 left-0 right-0`};
        height: 4px;
        background: ${({ $status }) => statusColor($status)};
        box-shadow: 0 0 18px ${({ $status }) => statusColor($status)};
    }

    &::after {
        content: '';
        ${tw`absolute inset-0 pointer-events-none`};
        background: radial-gradient(ellipse 90% 50% at 50% 0%, rgba(246, 226, 122, 0.06), transparent);
    }

    &:hover {
        ${tw`-translate-y-1`};
        border-color: rgb(var(--aurex-400) / 0.45);
        box-shadow: 0 18px 52px rgba(0, 0, 0, 0.6), 0 0 36px rgb(var(--aurex-400) / 0.18);
    }
`;

const StatusPill = styled.span<{ $status: ServerPowerState | undefined }>`
    ${tw`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-widest`};
    background: ${({ $status }) => statusColor($status)}1a;
    border: 1px solid ${({ $status }) => statusColor($status)}66;
    color: ${({ $status }) => statusColor($status)};
`;

const Led = styled.span<{ $status: ServerPowerState | undefined }>`
    ${tw`inline-block rounded-full flex-shrink-0`};
    width: 0.65rem;
    height: 0.65rem;
    background: ${({ $status }) => statusColor($status)};
    box-shadow: 0 0 10px ${({ $status }) => statusColor($status)};
    animation: ${ledPulse} 1.8s ease-in-out infinite;
`;

const IconBadge = styled.div`
    ${tw`flex items-center justify-center rounded-lg flex-shrink-0`};
    width: 3rem;
    height: 3rem;
    background: linear-gradient(150deg, rgba(216, 178, 74, 0.18), rgba(216, 178, 74, 0.04));
    border: 1px solid rgba(216, 178, 74, 0.35);
`;

const Bar = styled.div`
    ${tw`h-1.5 rounded-full bg-panel-bg overflow-hidden mt-1.5`};
`;

const Fill = styled.div<{ $pct: number; $alarm: boolean }>`
    height: 100%;
    border-radius: 9999px;
    width: ${({ $pct }) => Math.min(100, Math.max(0, $pct)).toFixed(1)}%;
    background: ${({ $alarm }) =>
        $alarm
            ? 'linear-gradient(90deg, #ef4444, #f87171)'
            : 'linear-gradient(90deg, #a87e1f, #f6e27a)'};
    transition: width 0.6s ease;
`;

const Icon = memo(
    styled(FontAwesomeIcon)<{ $alarm: boolean }>`
        ${(props) => (props.$alarm ? tw`text-red-400` : tw`text-primary-300`)};
    `,
    isEqual
);

const IconDescription = styled.p<{ $alarm: boolean }>`
    ${tw`text-sm ml-2 font-semibold`};
    ${(props) => (props.$alarm ? tw`text-white` : tw`text-panel-text`)};
`;

type Timer = ReturnType<typeof setInterval>;

export default ({ server, className }: { server: Server; className?: string }) => {
    const t = useTranslation();
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const [isSuspended, setIsSuspended] = useState(server.status === 'suspended');
    const [stats, setStats] = useState<ServerStats | null>(null);

    const getStats = () =>
        getServerResourceUsage(server.uuid)
            .then((data) => setStats(data))
            .catch((error) => console.error(error));

    useEffect(() => {
        setIsSuspended(stats?.isSuspended || server.status === 'suspended');
    }, [stats?.isSuspended, server.status]);

    useEffect(() => {
        // Don't waste a HTTP request if there is nothing important to show to the user because
        // the server is suspended.
        if (isSuspended || server.isNodeUnderMaintenance) return;

        getStats().then(() => {
            interval.current = setInterval(() => getStats(), 30000);
        });

        return () => {
            interval.current && clearInterval(interval.current);
        };
    }, [isSuspended, server.isNodeUnderMaintenance]);

    const alarms = { cpu: false, memory: false, disk: false };
    if (stats) {
        alarms.cpu = server.limits.cpu === 0 ? false : stats.cpuUsagePercent >= server.limits.cpu * 0.9;
        alarms.memory = isAlarmState(stats.memoryUsageInBytes, server.limits.memory);
        alarms.disk = server.limits.disk === 0 ? false : isAlarmState(stats.diskUsageInBytes, server.limits.disk);
    }

    const diskLimit = server.limits.disk !== 0 ? bytesToString(mbToBytes(server.limits.disk)) : t.serverRow.unlimited;
    const memoryLimit = server.limits.memory !== 0 ? bytesToString(mbToBytes(server.limits.memory)) : t.serverRow.unlimited;
    const cpuLimit = server.limits.cpu !== 0 ? server.limits.cpu + ' %' : t.serverRow.unlimited;

    const cpuPct = server.limits.cpu ? (stats ? (stats.cpuUsagePercent / server.limits.cpu) * 100 : 0) : 0;
    const memPct = server.limits.memory
        ? (stats ? (stats.memoryUsageInBytes / mbToBytes(server.limits.memory)) * 100 : 0)
        : 0;
    const diskPct = server.limits.disk
        ? (stats ? (stats.diskUsageInBytes / mbToBytes(server.limits.disk)) * 100 : 0)
        : 0;

    return (
        <Blade to={`/server/${server.id}`} className={className} $status={stats?.status}>
            {/* Header: icon + name + status */}
            <div css={tw`flex items-start justify-between gap-4 mb-5`}>
                <div css={tw`flex items-center gap-4 min-w-0`}>
                    <IconBadge>
                        <FontAwesomeIcon icon={faServer} size={'lg'} css={tw`text-primary-300`} />
                    </IconBadge>
                    <div css={tw`min-w-0`}>
                        <p css={tw`flex items-center gap-2 text-xl font-extrabold text-panel-text break-words`}>
                            <Led $status={stats?.status} />
                            <span css={tw`truncate`}>{server.name}</span>
                        </p>
                        {!!server.description && (
                            <p css={tw`text-sm text-panel-text-dim break-words line-clamp-1 mt-1`}>{server.description}</p>
                        )}
                        <p css={tw`text-xs text-panel-text-dim mt-1.5 flex items-center gap-1.5`}>
                            <FontAwesomeIcon icon={faEthernet} />
                            {server.allocations
                                .filter((alloc) => alloc.isDefault)
                                .map((allocation) => (
                                    <React.Fragment key={allocation.ip + allocation.port.toString()}>
                                        {allocation.alias || ip(allocation.ip)}:{allocation.port}
                                    </React.Fragment>
                                ))}
                        </p>
                    </div>
                </div>
                <StatusPill $status={stats?.status}>
                    {stats?.status === 'running' ? '● Online' : stats?.status === 'offline' ? '● Offline' : '● ' + (stats?.status || '…')}
                </StatusPill>
            </div>
            {/* Stats */}
            <div css={tw`pt-4`} style={{ borderTop: '1px solid rgb(var(--aurex-border) / 0.4)' }}>
                {!stats || isSuspended || server.isNodeUnderMaintenance ? (
                    isSuspended ? (
                        <div css={tw`flex items-center gap-3`}>
                            <span css={tw`bg-red-500/10 border border-red-500/40 rounded px-3 py-1 text-red-300 text-xs font-bold uppercase tracking-wide`}>
                                {server.status === 'suspended' ? t.serverRow.suspended : t.serverRow.connectionError}
                            </span>
                        </div>
                    ) : server.isNodeUnderMaintenance ? (
                        <div css={tw`flex items-center gap-3`}>
                            <span css={tw`bg-yellow-500/10 border border-yellow-500/40 rounded px-3 py-1 text-yellow-300 text-xs font-bold uppercase tracking-wide`}>
                                {t.serverRow.underMaintenance}
                            </span>
                        </div>
                    ) : server.isTransferring || server.status ? (
                        <div css={tw`flex items-center gap-3`}>
                            <span css={tw`bg-panel-text-dim/10 border border-panel-text-dim/40 rounded px-3 py-1 text-panel-text text-xs font-bold uppercase tracking-wide`}>
                                {server.isTransferring
                                    ? t.serverRow.transferring
                                    : server.status === 'installing'
                                    ? t.serverRow.installing
                                    : server.status === 'restoring_backup'
                                    ? t.serverRow.restoringBackup
                                    : t.serverRow.unavailable}
                            </span>
                        </div>
                    ) : (
                        <Spinner size={'small'} />
                    )
                ) : (
                    <div css={tw`grid grid-cols-3 gap-5`}>
                        <div>
                            <div css={tw`flex items-center justify-between mb-1`}>
                                <span css={tw`flex items-center gap-1.5`}>
                                    <Icon icon={faMicrochip} $alarm={alarms.cpu} />
                                    <IconDescription $alarm={alarms.cpu}>
                                        {stats.cpuUsagePercent.toFixed(0)}%
                                    </IconDescription>
                                </span>
                                <span css={tw`text-[10px] uppercase tracking-wider text-panel-text-dim font-bold`}>{t.serverRow.cpu}</span>
                            </div>
                            <Bar>
                                <Fill $pct={cpuPct} $alarm={alarms.cpu} />
                            </Bar>
                            <p css={tw`text-[11px] text-panel-text-dim mt-1.5`}>{t.serverRow.ofLimit(cpuLimit)}</p>
                        </div>
                        <div>
                            <div css={tw`flex items-center justify-between mb-1`}>
                                <span css={tw`flex items-center gap-1.5`}>
                                    <Icon icon={faMemory} $alarm={alarms.memory} />
                                    <IconDescription $alarm={alarms.memory}>
                                        {bytesToString(stats.memoryUsageInBytes)}
                                    </IconDescription>
                                </span>
                                <span css={tw`text-[10px] uppercase tracking-wider text-panel-text-dim font-bold`}>{t.serverRow.ram}</span>
                            </div>
                            <Bar>
                                <Fill $pct={memPct} $alarm={alarms.memory} />
                            </Bar>
                            <p css={tw`text-[11px] text-panel-text-dim mt-1.5`}>{t.serverRow.ofLimit(memoryLimit)}</p>
                        </div>
                        <div>
                            <div css={tw`flex items-center justify-between mb-1`}>
                                <span css={tw`flex items-center gap-1.5`}>
                                    <Icon icon={faHdd} $alarm={alarms.disk} />
                                    <IconDescription $alarm={alarms.disk}>
                                        {bytesToString(stats.diskUsageInBytes)}
                                    </IconDescription>
                                </span>
                                <span css={tw`text-[10px] uppercase tracking-wider text-panel-text-dim font-bold`}>{t.serverRow.disk}</span>
                            </div>
                            <Bar>
                                <Fill $pct={diskPct} $alarm={alarms.disk} />
                            </Bar>
                            <p css={tw`text-[11px] text-panel-text-dim mt-1.5`}>{t.serverRow.ofLimit(diskLimit)}</p>
                        </div>
                    </div>
                )}
            </div>
        </Blade>
    );
};
