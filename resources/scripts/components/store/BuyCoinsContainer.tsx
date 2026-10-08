import React, { useEffect, useState } from 'react';
import ContentBox from '@/components/elements/ContentBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import { useFlashKey } from '@/plugins/useFlash';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from 'easy-peasy';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCoins, faMoneyBillWave, faHistory, faStar, faFire } from '@fortawesome/free-solid-svg-icons';
import {
    getTopupOptions,
    submitTopup,
    getTopupHistory,
    TopupOptions,
    TopupPackage,
    TopupMethod,
    TopupRequest,
} from '@/api/store';
import useTranslation from '@/plugins/useTranslation';

const PackageGrid = styled.div`
    ${tw`grid gap-4 grid-cols-2 lg:grid-cols-4 mt-6`};
`;

/** Premium coin package card: deep surface, gold border glow on hover, lift. */
const PackageCard = styled.button<{ $featured?: boolean; $selected?: boolean }>`
    ${tw`relative rounded-2xl p-5 pt-7 flex flex-col items-center text-center cursor-pointer transition-all duration-300 overflow-visible`};
    background:
        linear-gradient(rgb(var(--aurex-surface-2)), rgb(var(--aurex-surface))) padding-box,
        linear-gradient(160deg, rgb(var(--aurex-400) / 0.55), rgb(var(--aurex-border) / 0.5) 55%, rgb(var(--aurex-500) / 0.35)) border-box;
    border: 1px solid transparent;
    box-shadow: 0 8px 24px rgb(0 0 0 / 0.4);

    &:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 36px rgb(0 0 0 / 0.5), 0 0 24px rgb(var(--aurex-500) / 0.3);
        background:
            linear-gradient(rgb(var(--aurex-surface-2)), rgb(var(--aurex-surface))) padding-box,
            linear-gradient(160deg, rgb(var(--aurex-300) / 0.9), rgb(var(--aurex-500) / 0.5) 55%, rgb(var(--aurex-400) / 0.6)) border-box;
    }

    ${(props) =>
        props.$selected &&
        `
        transform: translateY(-4px);
        box-shadow: 0 14px 36px rgb(0 0 0 / 0.5), 0 0 32px rgb(var(--aurex-500) / 0.55);
        background:
            linear-gradient(rgb(var(--aurex-surface-2)), rgb(var(--aurex-surface))) padding-box,
            linear-gradient(160deg, rgb(var(--aurex-300)), rgb(var(--aurex-500) / 0.7) 55%, rgb(var(--aurex-300))) border-box;
    `}
`;

/** Ribbon badge pinned to the top edge of a package card. */
const Ribbon = styled.span`
    ${tw`absolute -top-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest whitespace-nowrap`};
    background: linear-gradient(135deg, rgb(var(--aurex-300)), rgb(var(--aurex-600)));
    color: #1c130a;
    box-shadow: 0 4px 14px rgb(var(--aurex-500) / 0.5);
`;

const CoinAmount = styled.p`
    ${tw`mt-2 text-3xl font-black`};
    background: linear-gradient(115deg, rgb(var(--aurex-200)), rgb(var(--aurex-400)) 60%, rgb(var(--aurex-500)));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    filter: drop-shadow(0 0 10px rgb(var(--aurex-500) / 0.35));
`;

const PackagePrice = styled.p`
    ${tw`mt-2 text-xl font-black text-panel-text`};
`;

/** Gold gradient CTA used inside package cards. */
const CardBuyButton = styled.span`
    ${tw`mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-black uppercase tracking-wide text-xs transition-all duration-200`};
    background: linear-gradient(135deg, rgb(var(--aurex-300)), rgb(var(--aurex-500)) 55%, rgb(var(--aurex-600)));
    color: #1c130a;
    box-shadow: 0 4px 16px rgb(var(--aurex-500) / 0.3), inset 0 1px 0 rgb(255 255 255 / 0.3);

    ${PackageCard}:hover & {
        box-shadow: 0 8px 22px rgb(var(--aurex-500) / 0.5), inset 0 1px 0 rgb(255 255 255 / 0.3);
        filter: brightness(1.07);
    }
`;

const StatusBadge = styled.span<{ status: TopupRequest['status'] }>`
    ${tw`inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide`};
    ${(props) =>
        props.status === 'approved'
            ? tw`bg-green-500/10 text-green-400 border border-green-500/40`
            : props.status === 'rejected'
            ? tw`bg-red-500/10 text-red-400 border border-red-500/40`
            : tw`bg-primary-500/10 text-primary-300 border border-primary-500/40`}
`;

export default () => {
    const t = useTranslation();
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('topups');
    const { addFlash } = useFlash();
    const [options, setOptions] = useState<TopupOptions | null>(null);
    const [history, setHistory] = useState<TopupRequest[]>([]);
    const [buying, setBuying] = useState<TopupPackage | null>(null);
    const [method, setMethod] = useState<TopupMethod | null>(null);
    const [ref, setRef] = useState('');
    const [whatsapp, setWhatsapp] = useState('');
    const [email, setEmail] = useState('');
    const [submitting, setSubmitting] = useState(false);
    // Pre-fill the notification email with the user's account email (editable).
    const accountEmail = useStoreState((state) => state.user.data?.email ?? '');

    const loadHistory = () => {
        getTopupHistory()
            .then((res) => setHistory(res.data))
            .catch(() => {
                /* history is optional — options load is what matters */
            });
    };

    const load = () => {
        getTopupOptions()
            .then((opts) => {
                setOptions(opts);
                loadHistory();
            })
            .catch(clearAndAddHttpError);
    };

    useEffect(() => {
        load();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const startBuy = (pkg: TopupPackage) => {
        clearFlashes();
        setBuying(pkg);
        setMethod(null);
        setRef('');
        setWhatsapp('');
        setEmail(accountEmail);
    };

    const whatsappValid = /^0?3\d{9}$/.test(whatsapp.replace(/[\s-]/g, ''));
    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());

    const doSubmit = () => {
        if (!buying || !method || !ref.trim() || !whatsappValid || !emailValid) return;
        setSubmitting(true);
        clearFlashes();
        submitTopup(buying.id, method.key, ref.trim(), whatsapp.replace(/[\s-]/g, ''), email.trim())
            .then((result) => {
                addFlash({
                    key: 'topups',
                    type: 'success',
                    title: t.topup.requestSubmitted,
                    message: t.topup.requestPending(ref.trim()),
                });
                setBuying(null);
                setMethod(null);
                setRef('');
                setWhatsapp('');
                setEmail('');
                loadHistory();
            })
            .catch(clearAndAddHttpError)
            .then(() => setSubmitting(false));
    };

    const statusLabel = (status: TopupRequest['status']) =>
        status === 'approved' ? t.topup.statusApproved : status === 'rejected' ? t.topup.statusRejected : t.topup.statusPending;

    // Badges: best value = most coins per rupee; popular = 2nd cheapest package.
    const badgeFor = (pkg: TopupPackage): 'best' | 'popular' | null => {
        if (!options || options.packages.length < 2) return null;
        const byPrice = [...options.packages].sort((a, b) => a.price - b.price);
        const best = options.packages.reduce((m, p) => (p.coins / p.price > m.coins / m.price ? p : m));
        if (pkg.id === best.id) return 'best';
        if (byPrice.length >= 3 && pkg.id === byPrice[1].id) return 'popular';
        return null;
    };

    return (
        <ContentBox title={t.topup.title} showFlashes={'topups'} css={tw`mt-8`}>
            {!options ? (
                <Spinner centered />
            ) : !options.enabled || options.packages.length === 0 || options.methods.length === 0 ? (
                <p css={tw`text-panel-text-dim text-sm`}>{t.topup.disabled}</p>
            ) : (
                <div>
                    <p css={tw`text-panel-text-dim text-sm mb-2`}>{t.topup.tagline}</p>
                    <PackageGrid>
                        {options.packages.map((pkg, i) => {
                            const badge = badgeFor(pkg);
                            return (
                                <div
                                    key={pkg.id}
                                    className={'aurex-fade-up'}
                                    style={{ animationDelay: `${i * 80}ms` }}
                                >
                                    <PackageCard
                                        type={'button'}
                                        $selected={buying?.id === pkg.id}
                                        onClick={() => startBuy(pkg)}
                                    >
                                        {badge === 'popular' && (
                                            <Ribbon>
                                                <FontAwesomeIcon icon={faFire} />
                                                {t.topup.popularBadge}
                                            </Ribbon>
                                        )}
                                        {badge === 'best' && (
                                            <Ribbon>
                                                <FontAwesomeIcon icon={faStar} />
                                                {t.topup.bestValueBadge}
                                            </Ribbon>
                                        )}
                                        <h4 css={tw`text-sm font-bold uppercase tracking-wider text-panel-text-dim`}>
                                            {pkg.name}
                                        </h4>
                                        <CoinAmount>
                                            <FontAwesomeIcon icon={faCoins} css={tw`mr-2`} />
                                            {pkg.coins.toLocaleString()}
                                        </CoinAmount>
                                        <p css={tw`text-panel-text-dim text-xs mt-1 uppercase tracking-wide`}>
                                            {t.topup.coinsWord}
                                        </p>
                                        <PackagePrice>{t.topup.price(pkg.price, options?.currency_symbol)}</PackagePrice>
                                        <CardBuyButton>
                                            <FontAwesomeIcon icon={faMoneyBillWave} />
                                            {t.topup.buy}
                                        </CardBuyButton>
                                    </PackageCard>
                                </div>
                            );
                        })}
                    </PackageGrid>

                    {buying && (
                        <div
                            className={'aurex-fade-up'}
                            css={tw`mt-6 rounded-2xl p-[1px]`}
                            style={{
                                background:
                                    'linear-gradient(155deg, rgb(var(--aurex-400) / 0.7), rgb(var(--aurex-border) / 0.4))',
                                boxShadow: '0 12px 34px rgb(0 0 0 / 0.45), 0 0 26px rgb(var(--aurex-500) / 0.22)',
                            }}
                        >
                            <div css={tw`rounded-2xl p-5`} style={{ background: 'rgb(var(--aurex-surface-2))' }}>
                            <h4 css={tw`text-lg font-bold`} style={{ color: 'rgb(var(--aurex-300))' }}>
                                {buying.name} — {buying.coins.toLocaleString()} {t.topup.coinsWord} · {t.topup.price(buying.price, options?.currency_symbol)}
                            </h4>
                            {options && options.currency !== options.base_currency && (
                                <p css={tw`text-xs mt-1 opacity-70`}>
                                    ≈ {t.topup.price(buying.price_base, options.base_currency_symbol)} {options.base_currency} (charged amount)
                                </p>
                            )}

                            {!method ? (
                                <div css={tw`mt-4`}>
                                    <p css={tw`text-panel-text text-sm mb-3`}>{t.topup.chooseMethod}</p>
                                    <div css={tw`grid gap-3 md:grid-cols-2`}>
                                        {options.methods.map((m) => (
                                            <button
                                                key={m.key}
                                                type={'button'}
                                                onClick={() => setMethod(m)}
                                                css={tw`text-left px-4 py-3 rounded-xl cursor-pointer transition-all duration-150 hover:-translate-y-0.5`}
                                                style={{
                                                    border: '1px solid rgb(var(--aurex-border) / 0.6)',
                                                    background: 'rgb(var(--aurex-bg) / 0.5)',
                                                }}
                                                onMouseEnter={(e) => {
                                                    e.currentTarget.style.borderColor = 'rgb(var(--aurex-400) / 0.8)';
                                                    e.currentTarget.style.boxShadow = '0 0 18px rgb(var(--aurex-500) / 0.25)';
                                                }}
                                                onMouseLeave={(e) => {
                                                    e.currentTarget.style.borderColor = 'rgb(var(--aurex-border) / 0.6)';
                                                    e.currentTarget.style.boxShadow = 'none';
                                                }}
                                            >
                                                <span css={tw`font-bold text-panel-text`}>{m.label}</span>
                                            </button>
                                        ))}
                                    </div>
                                    <div css={tw`mt-4`}>
                                        <Button isSecondary onClick={() => setBuying(null)}>
                                            {t.topup.cancel}
                                        </Button>
                                    </div>
                                </div>
                            ) : (
                                <div css={tw`mt-4`}>
                                    <p css={tw`text-panel-text text-sm mb-3`}>{t.topup.payInstructions}</p>
                                    <div
                                        css={tw`rounded-xl p-4 mb-4`}
                                        style={{
                                            background: 'rgb(var(--aurex-bg) / 0.55)',
                                            border: '1px solid rgb(var(--aurex-500) / 0.35)',
                                        }}
                                    >
                                        <p css={tw`text-xs uppercase font-bold text-panel-text-dim`}>
                                            {t.topup.accountLabel}: {method.label}
                                        </p>
                                        <code
                                            css={tw`block mt-1 text-lg font-black break-all`}
                                            style={{
                                                color: 'rgb(var(--aurex-300))',
                                                textShadow: '0 0 14px rgb(var(--aurex-500) / 0.4)',
                                            }}
                                        >
                                            {method.account}
                                        </code>
                                        {method.instructions && (
                                            <p css={tw`text-panel-text-dim text-sm mt-2 whitespace-pre-wrap`}>
                                                {method.instructions}
                                            </p>
                                        )}
                                    </div>
                                    <div css={tw`mb-3`}>
                                        <div css={tw`flex-1 min-w-[220px] max-w-[320px]`}>
                                            <Input
                                                placeholder={t.topup.whatsappPlaceholder}
                                                value={whatsapp}
                                                maxLength={15}
                                                onChange={(e) => setWhatsapp(e.target.value)}
                                            />
                                        </div>
                                        {!whatsappValid && whatsapp.length > 0 && (
                                            <p css={tw`text-red-400 text-xs mt-1`}>{t.topup.whatsappInvalid}</p>
                                        )}
                                        <p css={tw`text-panel-text-dim text-xs mt-1`}>{t.topup.whatsappHint}</p>
                                    </div>
                                    <div css={tw`mb-3`}>
                                        <div css={tw`flex-1 min-w-[220px] max-w-[320px]`}>
                                            <Input
                                                type={'email'}
                                                placeholder={t.topup.emailPlaceholder}
                                                value={email}
                                                maxLength={255}
                                                onChange={(e) => setEmail(e.target.value)}
                                            />
                                        </div>
                                        {!emailValid && email.length > 0 && (
                                            <p css={tw`text-red-400 text-xs mt-1`}>{t.topup.emailInvalid}</p>
                                        )}
                                        <p css={tw`text-panel-text-dim text-xs mt-1`}>{t.topup.emailHint}</p>
                                    </div>
                                    <div css={tw`flex gap-3 items-end flex-wrap`}>
                                        <div css={tw`flex-1 min-w-[220px]`}>
                                            <Input
                                                placeholder={t.topup.refPlaceholder}
                                                value={ref}
                                                maxLength={100}
                                                onChange={(e) => setRef(e.target.value)}
                                            />
                                        </div>
                                        <Button
                                            color={'primary'}
                                            disabled={submitting || !ref.trim() || !whatsappValid || !emailValid}
                                            onClick={doSubmit}
                                        >
                                            {submitting ? t.topup.submitting : t.topup.submitRequest}
                                        </Button>
                                        <Button isSecondary onClick={() => setMethod(null)}>
                                            {t.topup.back}
                                        </Button>
                                    </div>
                                    <p css={tw`text-panel-text-dim text-xs mt-2`}>{t.topup.refHint}</p>
                                </div>
                            )}
                            </div>
                        </div>
                    )}

                    <div css={tw`mt-8`}>
                        <p css={tw`text-xs uppercase font-bold text-panel-text-dim mb-2`}>
                            <FontAwesomeIcon icon={faHistory} css={tw`mr-2`} />
                            {t.topup.myRequests}
                        </p>
                        {history.length === 0 ? (
                            <p css={tw`text-panel-text-dim text-sm`}>{t.topup.noRequests}</p>
                        ) : (
                            <div css={tw`divide-y`} style={{ borderColor: 'rgb(var(--aurex-border) / 0.4)' }}>
                                {history.map((r) => (
                                    <div key={r.id} css={tw`py-3`}>
                                        <div css={tw`flex items-center justify-between flex-wrap gap-2`}>
                                            <div>
                                                <span css={tw`font-bold text-panel-text`}>
                                                    {r.package ?? `#${r.id}`}
                                                </span>
                                                <span css={tw`text-panel-text-dim text-sm ml-2`}>
                                                    <FontAwesomeIcon icon={faCoins} css={tw`mr-1 text-primary-400`} />
                                                    {r.coins.toLocaleString()} · {t.topup.price(r.price, options?.currency_symbol)}
                                                </span>
                                            </div>
                                            <StatusBadge status={r.status}>{statusLabel(r.status)}</StatusBadge>
                                        </div>
                                        <p css={tw`text-panel-text-dim text-xs mt-1`}>
                                            {r.method} · {r.transaction_ref} ·{' '}
                                            {new Date(r.created_at).toLocaleDateString()}
                                        </p>
                                        {r.admin_note && (
                                            <p css={tw`text-panel-text-dim text-xs mt-1 italic`}>
                                                {t.topup.adminNote}: {r.admin_note}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </ContentBox>
    );
};
