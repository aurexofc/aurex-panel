import React, { useEffect, useState } from 'react';
import ContentBox from '@/components/elements/ContentBox';
import Button from '@/components/elements/Button';
import Spinner from '@/components/elements/Spinner';
import { useFlashKey } from '@/plugins/useFlash';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCoins, faPlay, faCheck } from '@fortawesome/free-solid-svg-icons';
import { getAdStatus, startAd, completeAd, AdStatus, AdSession } from '@/api/store';
import useTranslation from '@/plugins/useTranslation';

const DemoAd = styled.div`
    ${tw`rounded-lg bg-black border border-primary-500/50 p-10 text-center my-4`};
`;

interface Props {
    onEarned: (balance: number) => void;
}

export default ({ onEarned }: Props) => {
    const t = useTranslation();
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('ads');
    const { addFlash } = useFlash();
    const [status, setStatus] = useState<AdStatus | null>(null);
    const [session, setSession] = useState<AdSession | null>(null);
    const [countdown, setCountdown] = useState(0);
    const [busy, setBusy] = useState(false);
    const [cooldownTick, setCooldownTick] = useState(0);

    const refresh = () => {
        getAdStatus()
            .then(setStatus)
            .catch((error) => clearAndAddHttpError(error));
    };

    useEffect(() => {
        refresh();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Cooldown ticker.
    useEffect(() => {
        if (!status || status.cooldown_ends_in <= 0) return;
        if (cooldownTick >= status.cooldown_ends_in) {
            refresh();
            return;
        }
        const t = setTimeout(() => setCooldownTick((v) => v + 1), 1000);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [status, cooldownTick]);

    // Demo ad countdown.
    useEffect(() => {
        if (!session || session.ad_html || countdown <= 0) return;
        const t = setTimeout(() => setCountdown((v) => v - 1), 1000);
        return () => clearTimeout(t);
    }, [session, countdown]);

    const begin = () => {
        setBusy(true);
        clearFlashes();
        startAd()
            .then((s) => {
                setSession(s);
                setCountdown(s.demo_duration_seconds);
                setCooldownTick(0);
            })
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setBusy(false));
    };

    const claim = () => {
        if (!session) return;
        setBusy(true);
        completeAd(session.token)
            .then((result) => {
                setSession(null);
                onEarned(result.balance);
                addFlash({
                    key: 'ads',
                    type: 'success',
                    title: t.ads.coinsEarned,
                    message: t.ads.coinsAdded(result.coins_earned),
                });
                refresh();
            })
            .catch((error) => {
                clearAndAddHttpError(error);
                setSession(null);
                refresh();
            })
            .then(() => setBusy(false));
    };

    const remaining = status ? Math.max(0, status.cooldown_ends_in - cooldownTick) : 0;

    return (
        <ContentBox title={t.ads.title} showFlashes={'ads'} css={tw`mt-8`}>
            {!status ? (
                <Spinner centered />
            ) : !status.enabled ? (
                <p css={tw`text-panel-text-dim text-sm`}>{t.ads.disabled}</p>
            ) : session ? (
                <div>
                    {session.ad_html ? (
                        <div
                            css={tw`my-4 rounded-lg overflow-hidden`}
                            style={{ border: '1px solid rgb(var(--aurex-border) / 0.6)' }}
                            dangerouslySetInnerHTML={{ __html: session.ad_html }}
                        />
                    ) : (
                        <DemoAd>
                            <p css={tw`text-primary-400 font-bold text-2xl`}>AUREX</p>
                            <p css={tw`text-panel-text mt-2`}>
                                {t.ads.sponsored}
                            </p>
                            <p css={tw`text-panel-text-dim text-sm mt-4`}>
                                {countdown > 0
                                    ? t.ads.pleaseWait(countdown)
                                    : t.ads.thanksWatching}
                            </p>
                        </DemoAd>
                    )}
                    <div css={tw`flex gap-4`}>
                        <Button
                            color={'primary'}
                            disabled={busy || (!session.ad_html && countdown > 0)}
                            onClick={claim}
                        >
                            <FontAwesomeIcon icon={faCheck} css={tw`mr-2`} />
                            {busy ? t.ads.verifying : t.ads.claimCoins(session.reward_coins)}
                        </Button>
                        <Button disabled={busy} onClick={() => setSession(null)}>
                            {t.ads.cancel}
                        </Button>
                    </div>
                </div>
            ) : (
                <div css={tw`flex items-center justify-between flex-wrap gap-4`}>
                    <div css={tw`flex-1 min-w-[220px]`}>
                        <p css={tw`text-panel-text text-sm`}>
                            {t.ads.watchPrefix}{' '}
                            <span css={tw`font-bold`} style={{ color: 'rgb(var(--aurex-300))' }}>
                                <FontAwesomeIcon icon={faCoins} css={tw`mr-1`} />
                                {status.reward_coins} {t.ads.coinsWord}
                            </span>
                            .
                        </p>
                        <div css={tw`mt-3`}>
                            <div css={tw`flex items-center justify-between text-xs mb-1.5`}>
                                <span css={tw`text-panel-text-dim font-bold uppercase tracking-wide`}>
                                    {t.ads.watchedToday(status.watched_today, status.daily_limit)}
                                </span>
                                {remaining > 0 && (
                                    <span css={tw`text-panel-text-dim`}>{t.ads.nextAdIn(remaining)}</span>
                                )}
                            </div>
                            <div
                                css={tw`h-2.5 rounded-full overflow-hidden`}
                                style={{
                                    background: 'rgb(var(--aurex-bg) / 0.7)',
                                    border: '1px solid rgb(var(--aurex-border) / 0.5)',
                                }}
                            >
                                <div
                                    css={tw`h-full rounded-full transition-all duration-500`}
                                    style={{
                                        width: `${status.daily_limit > 0 ? Math.min(100, (status.watched_today / status.daily_limit) * 100) : 0}%`,
                                        background:
                                            'linear-gradient(90deg, rgb(var(--aurex-600)), rgb(var(--aurex-400)), rgb(var(--aurex-300)))',
                                        boxShadow: '0 0 12px rgb(var(--aurex-500) / 0.6)',
                                    }}
                                />
                            </div>
                        </div>
                    </div>
                    <Button
                        color={'primary'}
                        disabled={busy || !status.can_watch}
                        onClick={begin}
                        css={tw`px-8 py-3.5 text-base font-black uppercase tracking-wide`}
                        style={{ boxShadow: '0 0 24px rgb(var(--aurex-500) / 0.4)' }}
                    >
                        <FontAwesomeIcon icon={faPlay} css={tw`mr-2`} />
                        {busy ? t.ads.loading : t.ads.watchAd}
                    </Button>
                </div>
            )}
        </ContentBox>
    );
};
