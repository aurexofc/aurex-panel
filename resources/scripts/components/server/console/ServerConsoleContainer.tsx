import React, { memo } from 'react';
import { ServerContext } from '@/state/server';
import Can from '@/components/elements/Can';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import isEqual from 'react-fast-compare';
import Spinner from '@/components/elements/Spinner';
import Features from '@feature/Features';
import Console from '@/components/server/console/Console';
import StatGraphs from '@/components/server/console/StatGraphs';
import PowerButtons from '@/components/server/console/PowerButtons';
import ServerDetailsBlock from '@/components/server/console/ServerDetailsBlock';
import { Alert } from '@/components/elements/alert';
import tw from 'twin.macro';
import styled, { keyframes } from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faServer, faCircle } from '@fortawesome/free-solid-svg-icons';

const statusGlow = keyframes`
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
`;

const HeaderCard = styled.div`
    ${tw`relative rounded-2xl p-6 mb-6 overflow-hidden`};
    background:
        radial-gradient(ellipse 60% 80% at 90% 10%, rgba(216, 178, 74, 0.12), transparent),
        linear-gradient(150deg, rgb(var(--aurex-surface-2)) 0%, rgb(var(--aurex-surface)) 55%, rgb(var(--aurex-bg)) 100%);
    border: 1px solid rgb(var(--aurex-400) / 0.22);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(246, 226, 122, 0.1);
`;

const ServerTitle = styled.h1`
    ${tw`text-2xl md:text-3xl font-black tracking-tight flex items-center gap-3`};
    background: linear-gradient(115deg, #f6e27a 0%, #d8b24a 50%, #f6e27a 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
`;

const StatusDot = styled.span<{ $online: boolean }>`
    ${tw`inline-block rounded-full`};
    width: 0.7rem;
    height: 0.7rem;
    background: ${({ $online }) => ($online ? '#4ade80' : '#f87171')};
    box-shadow: 0 0 12px ${({ $online }) => ($online ? '#4ade80' : '#f87171')};
    animation: ${statusGlow} 2s ease-in-out infinite;
`;

export type PowerAction = 'start' | 'stop' | 'restart' | 'kill';

const ServerConsoleContainer = () => {
    const name = ServerContext.useStoreState((state) => state.server.data!.name);
    const description = ServerContext.useStoreState((state) => state.server.data!.description);
    const isInstalling = ServerContext.useStoreState((state) => state.server.isInstalling);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data!.isTransferring);
    const eggFeatures = ServerContext.useStoreState((state) => state.server.data!.eggFeatures, isEqual);
    const isNodeUnderMaintenance = ServerContext.useStoreState((state) => state.server.data!.isNodeUnderMaintenance);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const isOnline = status === 'running';

    return (
        <ServerContentBlock title={'Console'}>
            {(isNodeUnderMaintenance || isInstalling || isTransferring) && (
                <Alert type={'warning'} className={'mb-4'}>
                    {isNodeUnderMaintenance
                        ? 'The node of this server is currently under maintenance and all actions are unavailable.'
                        : isInstalling
                        ? 'This server is currently running its installation process and most actions are unavailable.'
                        : 'This server is currently being transferred to another node and all actions are unavailable.'}
                </Alert>
            )}
            {/* VIP Server header */}
            <HeaderCard>
                <div css={tw`flex items-center justify-between flex-wrap gap-4`}>
                    <div css={tw`flex items-center gap-4 min-w-0`}>
                        <div
                            css={tw`flex items-center justify-center rounded-xl flex-shrink-0`}
                            style={{
                                width: '3.5rem',
                                height: '3.5rem',
                                background: 'linear-gradient(150deg, rgba(216, 178, 74, 0.22), rgba(216, 178, 74, 0.05))',
                                border: '1px solid rgba(216, 178, 74, 0.4)',
                                boxShadow: '0 0 20px rgba(216, 178, 74, 0.15)',
                            }}
                        >
                            <FontAwesomeIcon icon={faServer} size={'lg'} css={tw`text-primary-300`} />
                        </div>
                        <div css={tw`min-w-0`}>
                            <ServerTitle>
                                <StatusDot $online={isOnline} />
                                <span css={tw`truncate`}>{name}</span>
                            </ServerTitle>
                            {!!description && (
                                <p css={tw`text-sm text-panel-text-dim mt-1 truncate`}>{description}</p>
                            )}
                        </div>
                    </div>
                    <div css={tw`flex-shrink-0`}>
                        <Can action={['control.start', 'control.stop', 'control.restart']} matchAny>
                            <PowerButtons className={'flex space-x-2'} />
                        </Can>
                    </div>
                </div>
            </HeaderCard>
            <div className={'grid grid-cols-4 gap-2 sm:gap-4 mb-4'}>
                <div className={'flex col-span-4 lg:col-span-3'}>
                    <Spinner.Suspense>
                        <Console />
                    </Spinner.Suspense>
                </div>
                <ServerDetailsBlock className={'col-span-4 lg:col-span-1 order-last lg:order-none'} />
            </div>
            <div className={'grid grid-cols-1 md:grid-cols-3 gap-2 sm:gap-4'}>
                <Spinner.Suspense>
                    <StatGraphs />
                </Spinner.Suspense>
            </div>
            <Features enabled={eggFeatures} />
        </ServerContentBlock>
    );
};

export default memo(ServerConsoleContainer, isEqual);
