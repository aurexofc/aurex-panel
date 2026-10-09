import React, { useEffect, useState } from 'react';
import PageContentBlock from '@/components/elements/PageContentBlock';
import ContentBox from '@/components/elements/ContentBox';
import Button from '@/components/elements/Button';
import Spinner from '@/components/elements/Spinner';
import { useFlashKey } from '@/plugins/useFlash';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faCoins,
    faCrown,
    faCheck,
    faServer,
    faAd,
    faInfinity,
} from '@fortawesome/free-solid-svg-icons';
import { getPremiumStatus, purchasePremium, PremiumStatus } from '@/api/premium';
import useTranslation from '@/plugins/useTranslation';
import { useStoreActions } from 'easy-peasy';
import { Actions } from 'easy-peasy';
import { ApplicationStore } from '@/state';

const PackageGrid = styled.div`
    ${tw`grid gap-6 md:grid-cols-2 xl:grid-cols-4 mt-6`};
`;

const PackageCard = styled.div<{ $highlight?: boolean }>`
    ${tw`rounded-2xl flex flex-col relative overflow-hidden transition-all duration-300 p-6 text-center`};
    background:
        linear-gradient(rgb(var(--aurex-surface-2)), rgb(var(--aurex-surface))) padding-box,
        linear-gradient(155deg, rgb(var(--aurex-300) / 0.85), rgb(var(--aurex-600) / 0.25) 38%, rgb(var(--aurex-border) / 0.6) 72%, rgb(var(--aurex-400) / 0.5)) border-box;
    border: 1px solid transparent;
    box-shadow: 0 10px 30px rgb(0 0 0 / 0.45);
    ${(props) =>
        props.$highlight &&
        `
        transform: scale(1.04);
        box-shadow: 0 18px 44px rgb(0 0 0 / 0.55), 0 0 32px rgb(var(--aurex-500) / 0.35);
    `}

    &:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 44px rgb(0 0 0 / 0.55), 0 0 28px rgb(var(--aurex-500) / 0.28);
    }
`;

const BestBadge = styled.div`
    ${tw`absolute top-4 right-4 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider`};
    background: linear-gradient(115deg, rgb(var(--aurex-300)), rgb(var(--aurex-500)));
    color: rgb(var(--aurex-bg));
`;

const PriceTag = styled.div`
    ${tw`text-3xl font-bold my-3`};
    color: rgb(var(--aurex-300));
`;

const PerkList = styled.ul`
    ${tw`text-sm text-left space-y-2 my-4 flex-1`};
    & li {
        ${tw`flex items-center gap-2`};
    }
    & svg {
        color: rgb(var(--aurex-400));
    }
`;

export default () => {
    const t = useTranslation();
    const { addFlash, clearFlashes } = useFlash();
    const { clearAndAddHttpError } = useFlashKey('premium');
    const [status, setStatus] = useState<PremiumStatus | null>(null);
    const [buying, setBuying] = useState<number | null>(null);
    const updateUserData = useStoreActions((actions: Actions<ApplicationStore>) => actions.user.updateUserData);

    useEffect(() => {
        getPremiumStatus().then(setStatus).catch(clearAndAddHttpError);
    }, []);

    const onBuy = (packageId: number) => {
        setBuying(packageId);
        clearFlashes('premium');
        purchasePremium(packageId)
            .then((result) => {
                updateUserData({ isPremium: true });
                addFlash({ type: 'success', key: 'premium', message: result.message });
                return getPremiumStatus().then(setStatus);
            })
            .catch((error) => {
                addFlash({
                    type: 'error',
                    key: 'premium',
                    message: error?.response?.data?.message || error?.message || t.premium.failed,
                });
            })
            .finally(() => setBuying(null));
    };

    return (
        <PageContentBlock title={t.premium.title}>
            {!status ? (
                <Spinner centered />
            ) : (
                <>
                    {status.is_premium && status.subscription && (
                        <ContentBox css={tw`mb-6 text-center`}>
                            <div css={tw`text-4xl mb-2`}>
                                <FontAwesomeIcon icon={faCrown} style={{ color: 'rgb(var(--aurex-400))' }} />
                            </div>
                            <p css={tw`text-lg font-bold`} style={{ color: 'rgb(var(--aurex-300))' }}>
                                {t.premium.activeTitle}
                            </p>
                            <p css={tw`text-sm opacity-70`}>
                                {status.subscription.package_name} •{' '}
                                {status.subscription.is_lifetime
                                    ? t.premium.lifetime
                                    : `${t.premium.expires}: ${new Date(
                                          status.subscription.expires_at!
                                      ).toLocaleDateString()}`}
                            </p>
                        </ContentBox>
                    )}

                    <div css={tw`text-center mb-2`}>
                        <h2 css={tw`text-2xl font-bold`} style={{ color: 'rgb(var(--aurex-300))' }}>
                            <FontAwesomeIcon icon={faCrown} /> {t.premium.title}
                        </h2>
                        <p css={tw`text-sm opacity-70 mt-1`}>{t.premium.subtitle}</p>
                        <p css={tw`text-sm mt-2`}>
                            <FontAwesomeIcon icon={faCoins} style={{ color: 'rgb(var(--aurex-400))' }} />{' '}
                            {status.balance.toLocaleString()} {t.premium.coins}
                        </p>
                    </div>

                    <PackageGrid>
                        {status.packages.map((pkg, i) => (
                            <PackageCard key={pkg.id} $highlight={pkg.slug === 'yearly'}>
                                {pkg.slug === 'yearly' && <BestBadge>{t.premium.bestValue}</BestBadge>}
                                <div css={tw`text-4xl mb-2`}>
                                    <FontAwesomeIcon
                                        icon={faCrown}
                                        style={{ color: 'rgb(var(--aurex-400))' }}
                                    />
                                </div>
                                <h3 css={tw`text-xl font-bold`}>{pkg.name}</h3>
                                <p css={tw`text-xs opacity-60 uppercase tracking-wider`}>
                                    {pkg.duration_label}
                                </p>
                                <PriceTag>
                                    <FontAwesomeIcon icon={faCoins} /> {pkg.price_coins.toLocaleString()}
                                </PriceTag>
                                <PerkList>
                                    <li>
                                        <FontAwesomeIcon icon={faCheck} /> {t.premium.perkAdFree}
                                    </li>
                                    <li>
                                        <FontAwesomeIcon icon={faCheck} />{' '}
                                        <FontAwesomeIcon icon={faServer} /> {pkg.max_servers}{' '}
                                        {t.premium.perkServers}
                                    </li>
                                    <li>
                                        <FontAwesomeIcon icon={faCheck} /> {t.premium.perkBadge}
                                    </li>
                                    {pkg.is_lifetime && (
                                        <li>
                                            <FontAwesomeIcon icon={faCheck} />{' '}
                                            <FontAwesomeIcon icon={faInfinity} /> {t.premium.perkLifetime}
                                        </li>
                                    )}
                                </PerkList>
                                <Button
                                    disabled={buying !== null}
                                    onClick={() => onBuy(pkg.id)}
                                    css={tw`w-full`}
                                >
                                    {buying === pkg.id ? (
                                        <Spinner size={'small'} centered />
                                    ) : (
                                        t.premium.buy
                                    )}
                                </Button>
                            </PackageCard>
                        ))}
                    </PackageGrid>
                    {status.packages.length === 0 && (
                        <p css={tw`text-center opacity-60 mt-8`}>{t.premium.empty}</p>
                    )}
                </>
            )}
        </PageContentBlock>
    );
};
