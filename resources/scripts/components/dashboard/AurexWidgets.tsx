import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import tw from 'twin.macro';
import styled, { keyframes } from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCoins, faUsers, faBullhorn, faTimes, faRocket } from '@fortawesome/free-solid-svg-icons';
import { getAnnouncements, Announcement } from '@/api/announcements';
import { getStore } from '@/api/store';
import { getReferrals } from '@/api/referrals';
import useTranslation from '@/plugins/useTranslation';
import useCountUp from '@/plugins/useCountUp';

const glowPulse = keyframes`
    0%, 100% { box-shadow: 0 0 22px rgb(var(--aurex-400) / 0.12); }
    50% { box-shadow: 0 0 40px rgb(var(--aurex-400) / 0.28); }
`;

const CardGrid = styled.div`
    ${tw`grid gap-4 md:grid-cols-3 mb-6`};
`;

const WidgetCard = styled(Link)`
    ${tw`relative rounded-xl p-5 flex items-center gap-4 no-underline overflow-hidden transition-all duration-200`};
    background: linear-gradient(150deg, rgb(var(--aurex-surface)) 0%, rgb(var(--aurex-bg)) 70%);
    border: 1px solid rgb(var(--aurex-border) / 0.55);

    &::before {
        content: '';
        ${tw`absolute top-0 left-0 right-0`};
        height: 3px;
        background: linear-gradient(90deg, transparent, #d8b24a, transparent);
        opacity: 0.65;
    }

    &:hover {
        ${tw`-translate-y-0.5`};
        border-color: rgb(var(--aurex-400) / 0.55);
        animation: ${glowPulse} 2.2s ease-in-out infinite;
    }
`;

const IconBadge = styled.div`
    ${tw`flex items-center justify-center rounded-full flex-shrink-0`};
    width: 3.25rem;
    height: 3.25rem;
    background: radial-gradient(circle at 35% 30%, rgba(246, 226, 122, 0.3), rgba(216, 178, 74, 0.07));
    border: 1px solid rgba(216, 178, 74, 0.45);
`;

const Banner = styled.div`
    ${tw`rounded-xl p-4 mb-4 flex items-start gap-3`};
    background: linear-gradient(150deg, rgba(216, 178, 74, 0.13), rgba(216, 178, 74, 0.03));
    border: 1px solid rgba(216, 178, 74, 0.35);
`;

const dismissedKey = (id: number) => `aurex:announcement:dismissed:${id}`;

export default () => {
    const t = useTranslation();
    const [announcements, setAnnouncements] = useState<Announcement[]>([]);
    const [balance, setBalance] = useState<number | null>(null);
    const [referralCount, setReferralCount] = useState<number | null>(null);

    // Animated count-up for the stat numbers (eases from 0 on load).
    const animatedBalance = useCountUp(balance);
    const animatedReferrals = useCountUp(referralCount);

    useEffect(() => {
        getAnnouncements()
            .then((a) => setAnnouncements(a.filter((x) => !localStorage.getItem(dismissedKey(x.id)))))
            .catch(() => undefined);
        getStore()
            .then((s) => setBalance(s.balance))
            .catch(() => undefined);
        getReferrals()
            .then((r) => setReferralCount(r.total_invited))
            .catch(() => undefined);
    }, []);

    const dismiss = (id: number) => {
        localStorage.setItem(dismissedKey(id), '1');
        setAnnouncements((prev) => prev.filter((a) => a.id !== id));
    };

    return (
        <div css={tw`mt-6`}>
            {announcements.map((a) => (
                <Banner key={a.id}>
                    <FontAwesomeIcon icon={faBullhorn} css={tw`text-primary-400 mt-1`} />
                    <div css={tw`flex-1`}>
                        <p css={tw`font-bold text-primary-200`}>{a.title}</p>
                        <p css={tw`text-sm text-panel-text-dim mt-1 whitespace-pre-wrap`}>{a.body}</p>
                    </div>
                    <button onClick={() => dismiss(a.id)} css={tw`text-panel-text-dim hover:text-panel-text`}>
                        <FontAwesomeIcon icon={faTimes} />
                    </button>
                </Banner>
            ))}

            <CardGrid>
                <div className={'aurex-fade-up'} style={{ animationDelay: '60ms' }}>
                    <WidgetCard to={'/store'}>
                        <IconBadge>
                            <FontAwesomeIcon icon={faCoins} size={'lg'} css={tw`text-primary-300`} />
                        </IconBadge>
                        <div>
                            <p css={tw`text-xs uppercase text-panel-text-dim font-bold tracking-wider`}>{t.widgets.coinBalance}</p>
                            <p css={tw`text-2xl font-extrabold text-panel-text mt-0.5`}>
                                {animatedBalance === null ? '…' : (
                                    <>
                                        {animatedBalance.toLocaleString()} <span css={tw`text-sm font-bold text-primary-300`}>{t.widgets.coins}</span>
                                    </>
                                )}
                            </p>
                        </div>
                    </WidgetCard>
                </div>
                <div className={'aurex-fade-up'} style={{ animationDelay: '120ms' }}>
                    <WidgetCard to={'/store'}>
                        <IconBadge>
                            <FontAwesomeIcon icon={faUsers} size={'lg'} css={tw`text-primary-300`} />
                        </IconBadge>
                        <div>
                            <p css={tw`text-xs uppercase text-panel-text-dim font-bold tracking-wider`}>{t.widgets.friendsInvited}</p>
                            <p css={tw`text-2xl font-extrabold text-panel-text mt-0.5`}>
                                {animatedReferrals === null ? '…' : animatedReferrals}
                            </p>
                        </div>
                    </WidgetCard>
                </div>
                <div className={'aurex-fade-up'} style={{ animationDelay: '180ms' }}>
                    <WidgetCard to={'/store'}>
                        <IconBadge>
                            <FontAwesomeIcon icon={faRocket} size={'lg'} css={tw`text-primary-300`} />
                        </IconBadge>
                        <div>
                            <p css={tw`text-xs uppercase text-panel-text-dim font-bold tracking-wider`}>{t.widgets.deployServer}</p>
                            <p css={tw`text-lg font-extrabold text-primary-200 mt-0.5`}>{t.widgets.visitStoreCta}</p>
                        </div>
                    </WidgetCard>
                </div>
            </CardGrid>
        </div>
    );
};
