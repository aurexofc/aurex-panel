import React, { useEffect, useState } from 'react';
import PageContentBlock from '@/components/elements/PageContentBlock';
import ContentBox from '@/components/elements/ContentBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import { useFlashKey } from '@/plugins/useFlash';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faCoins,
    faMemory,
    faMicrochip,
    faHdd,
    faShoppingCart,
    faCrown,
} from '@fortawesome/free-solid-svg-icons';
import { getStore, getLedger, purchasePlan, ServerPlan, CoinEntry } from '@/api/store';
import EarnCoinsContainer from '@/components/store/EarnCoinsContainer';
import BuyCoinsContainer from '@/components/store/BuyCoinsContainer';
import ReferralsContainer from '@/components/store/ReferralsContainer';
import useTranslation from '@/plugins/useTranslation';

const PlanGrid = styled.div`
    ${tw`grid gap-6 md:grid-cols-2 xl:grid-cols-3 mt-6`};
`;

/** Premium server plan card: gradient gold border, deep surface, hover lift + glow. */
const PlanCard = styled.div`
    ${tw`rounded-2xl flex flex-col relative overflow-hidden transition-all duration-300`};
    background:
        linear-gradient(rgb(var(--aurex-surface-2)), rgb(var(--aurex-surface))) padding-box,
        linear-gradient(155deg, rgb(var(--aurex-300) / 0.85), rgb(var(--aurex-600) / 0.25) 38%, rgb(var(--aurex-border) / 0.6) 72%, rgb(var(--aurex-400) / 0.5)) border-box;
    border: 1px solid transparent;
    box-shadow: 0 10px 30px rgb(0 0 0 / 0.45);

    &:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 44px rgb(0 0 0 / 0.55), 0 0 28px rgb(var(--aurex-500) / 0.28);
    }
`;

const PlanName = styled.h3`
    ${tw`text-2xl font-black tracking-wide`};
    background: linear-gradient(115deg, rgb(var(--aurex-200)), rgb(var(--aurex-400)) 55%, rgb(var(--aurex-500)));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
`;

const SpecGrid = styled.div`
    ${tw`grid grid-cols-3 gap-2 mt-4`};
`;

const SpecCell = styled.div`
    ${tw`rounded-xl px-2 py-3 text-center`};
    background: rgb(var(--aurex-bg) / 0.55);
    border: 1px solid rgb(var(--aurex-border) / 0.5);
`;

const SpecIcon = styled.div`
    ${tw`text-lg mb-1`};
    color: rgb(var(--aurex-400));
`;

const SpecValue = styled.div`
    ${tw`text-sm font-bold text-panel-text leading-tight`};
`;

const SpecLabel = styled.div`
    ${tw`text-[10px] uppercase tracking-wider text-panel-text-dim mt-0.5`};
`;

const DurationBadge = styled.span`
    ${tw`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold`};
    background: rgb(var(--aurex-500) / 0.14);
    border: 1px solid rgb(var(--aurex-500) / 0.45);
    color: rgb(var(--aurex-300));
`;

/** Gold gradient BUY button with hover lift + glow. */
const GoldButton = styled.button`
    ${tw`inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-black uppercase tracking-wide text-sm cursor-pointer transition-all duration-200`};
    background: linear-gradient(135deg, rgb(var(--aurex-300)), rgb(var(--aurex-500)) 55%, rgb(var(--aurex-600)));
    color: #1c130a;
    border: none;
    box-shadow: 0 6px 20px rgb(var(--aurex-500) / 0.35), inset 0 1px 0 rgb(255 255 255 / 0.35);

    &:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgb(var(--aurex-500) / 0.5), inset 0 1px 0 rgb(255 255 255 / 0.35);
        filter: brightness(1.06);
    }

    &:active:not(:disabled) {
        transform: translateY(0);
    }

    &:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        box-shadow: none;
    }
`;

const BalanceBadge = styled.div`
    ${tw`inline-flex items-center gap-2 px-5 py-3 rounded-full font-black text-xl`};
    background: linear-gradient(135deg, rgb(var(--aurex-500) / 0.22), rgb(var(--aurex-600) / 0.08));
    border: 1px solid rgb(var(--aurex-400) / 0.55);
    color: rgb(var(--aurex-200));
    box-shadow: 0 0 26px rgb(var(--aurex-500) / 0.28), inset 0 1px 0 rgb(255 255 255 / 0.08);
`;

const PriceTag = styled.span`
    ${tw`text-2xl font-black`};
    color: rgb(var(--aurex-300));
    text-shadow: 0 0 18px rgb(var(--aurex-500) / 0.4);
`;

export default () => {
    const t = useTranslation();
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('store');
    const { addFlash } = useFlash();
    const [loading, setLoading] = useState(true);
    const [balance, setBalance] = useState(0);
    const [plans, setPlans] = useState<ServerPlan[]>([]);
    const [entries, setEntries] = useState<CoinEntry[]>([]);
    const [buying, setBuying] = useState<ServerPlan | null>(null);
    const [serverName, setServerName] = useState('');
    const [purchasing, setPurchasing] = useState(false);

    const load = (withSpinner = true) => {
        if (withSpinner) setLoading(true);
        Promise.all([getStore(), getLedger()])
            .then(([store, ledger]) => {
                setBalance(store.balance);
                setPlans(store.plans);
                setEntries(ledger.entries);
                setLoading(false);
            })
            .catch((error) => {
                clearAndAddHttpError(error);
                setLoading(false);
            });
    };

    useEffect(() => {
        clearFlashes();
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const doPurchase = () => {
        if (!buying || !serverName.trim()) return;
        setPurchasing(true);
        clearFlashes();
        purchasePlan(buying.id, serverName.trim())
            .then((result) => {
                setBalance(result.balance);
                setBuying(null);
                setServerName('');
                addFlash({ key: 'store', type: 'success', title: t.store.serverCreated, message: result.message });
                load();
            })
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setPurchasing(false));
    };

    return (
        <PageContentBlock title={t.store.title} showFlashKey={'store'}>
            <div css={tw`flex items-center justify-between mt-6 flex-wrap gap-4`}>
                <p css={tw`text-panel-text-dim`}>
                    {t.store.tagline}
                </p>
                <BalanceBadge>
                    <FontAwesomeIcon icon={faCoins} />
                    {t.store.balanceCoins(balance.toLocaleString())}
                </BalanceBadge>
            </div>

            {loading ? (
                <Spinner centered />
            ) : plans.length === 0 ? (
                <p css={tw`text-panel-text-dim mt-8 text-center`}>
                    {t.store.noPlans}
                </p>
            ) : (
                <PlanGrid>
                    {plans.map((plan, i) => {
                        const affordable = balance >= plan.price_coins;
                        return (
                            <div
                                key={plan.id}
                                className={'aurex-fade-up'}
                                style={{ animationDelay: `${i * 90}ms` }}
                            >
                                <PlanCard>
                                    <div css={tw`p-6 flex flex-col h-full`}>
                                        <div css={tw`flex items-center gap-2`}>
                                            <FontAwesomeIcon
                                                icon={faCrown}
                                                css={tw`text-lg`}
                                                style={{ color: 'rgb(var(--aurex-400))' }}
                                            />
                                            <PlanName>{plan.name}</PlanName>
                                        </div>
                                        <p css={tw`text-panel-text-dim text-sm mt-2 flex-1`}>{plan.description}</p>

                                        <SpecGrid>
                                            <SpecCell>
                                                <SpecIcon>
                                                    <FontAwesomeIcon icon={faMemory} />
                                                </SpecIcon>
                                                <SpecValue>{plan.memory.toLocaleString()} MB</SpecValue>
                                                <SpecLabel>RAM</SpecLabel>
                                            </SpecCell>
                                            <SpecCell>
                                                <SpecIcon>
                                                    <FontAwesomeIcon icon={faMicrochip} />
                                                </SpecIcon>
                                                <SpecValue>{plan.cpu}%</SpecValue>
                                                <SpecLabel>CPU</SpecLabel>
                                            </SpecCell>
                                            <SpecCell>
                                                <SpecIcon>
                                                    <FontAwesomeIcon icon={faHdd} />
                                                </SpecIcon>
                                                <SpecValue>{(plan.disk / 1024).toLocaleString()} GB</SpecValue>
                                                <SpecLabel>Disk</SpecLabel>
                                            </SpecCell>
                                        </SpecGrid>

                                        <div css={tw`mt-4`}>
                                            <DurationBadge>{t.store.daysIncluded(plan.duration_days)}</DurationBadge>
                                        </div>

                                        <div css={tw`mt-5 pt-5 flex items-center justify-between gap-3`}
                                            style={{ borderTop: '1px solid rgb(var(--aurex-border) / 0.5)' }}
                                        >
                                            <PriceTag>
                                                <FontAwesomeIcon icon={faCoins} css={tw`mr-2 text-xl`} />
                                                {plan.price_coins.toLocaleString()}
                                            </PriceTag>
                                            <GoldButton disabled={!affordable} onClick={() => setBuying(plan)}>
                                                <FontAwesomeIcon icon={faShoppingCart} />
                                                {t.store.buy}
                                            </GoldButton>
                                        </div>
                                        {!affordable && (
                                            <p css={tw`text-xs text-panel-text-dim mt-2 text-right`}>
                                                {t.store.notEnoughCoins}
                                            </p>
                                        )}
                                    </div>
                                </PlanCard>
                            </div>
                        );
                    })}
                </PlanGrid>
            )}

            {buying && (
                <ContentBox title={t.store.buyTitle(buying.name)} css={tw`mt-8`}>
                    <p css={tw`text-panel-text-dim mb-4`}>
                        {t.store.purchaseHint(buying.price_coins.toLocaleString())}
                    </p>
                    <div css={tw`flex gap-4 items-end flex-wrap`}>
                        <div css={tw`flex-1 min-w-[200px]`}>
                            <Input
                                placeholder={t.store.serverNamePlaceholder}
                                value={serverName}
                                onChange={(e) => setServerName(e.target.value)}
                            />
                        </div>
                        <Button color={'primary'} disabled={purchasing || !serverName.trim()} onClick={doPurchase}>
                            {purchasing ? t.store.creating : t.store.confirmPurchase(buying.price_coins.toLocaleString())}
                        </Button>
                        <Button onClick={() => setBuying(null)}>{t.store.cancel}</Button>
                    </div>
                </ContentBox>
            )}

            <EarnCoinsContainer
                onEarned={() => {
                    load(false);
                }}
            />

            <BuyCoinsContainer />

            <ReferralsContainer onClaimed={() => load(false)} />

            <ContentBox title={t.store.recentActivity} css={tw`mt-8`}>
                {entries.length === 0 ? (
                    <p css={tw`text-panel-text-dim text-sm`}>{t.store.noActivity}</p>
                ) : (
                    <div css={tw`divide-y`} style={{ borderColor: 'rgb(var(--aurex-border) / 0.4)' }}>
                        {entries.map((entry) => (
                            <div key={entry.id} css={tw`flex items-center justify-between py-2 text-sm`}>
                                <span css={tw`text-panel-text`}>
                                    {t.store.reasons[entry.reason] ?? entry.reason}
                                </span>
                                <span
                                    css={[
                                        tw`font-bold`,
                                        entry.amount >= 0 ? tw`text-green-400` : tw`text-red-400`,
                                    ]}
                                >
                                    {entry.amount >= 0 ? '+' : ''}
                                    {entry.amount.toLocaleString()}
                                </span>
                            </div>
                        ))}
                    </div>
                )}
            </ContentBox>
        </PageContentBlock>
    );
};
