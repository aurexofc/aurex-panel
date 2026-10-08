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
    faRocket,
    faCrown,
    faRobot,
    faQrcode,
} from '@fortawesome/free-solid-svg-icons';
import { getPrebots, purchasePrebot, Prebot } from '@/api/prebots';
import useTranslation from '@/plugins/useTranslation';

const PrebotGrid = styled.div`
    ${tw`grid gap-6 md:grid-cols-2 xl:grid-cols-3 mt-6`};
`;

const PrebotCard = styled.div`
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

const FeaturedBadge = styled.div`
    ${tw`absolute top-4 right-4 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider`};
    background: linear-gradient(115deg, rgb(var(--aurex-300)), rgb(var(--aurex-500)));
    color: rgb(var(--aurex-bg));
`;

const PrebotIcon = styled.div`
    ${tw`text-5xl mb-3`};
`;

const PrebotName = styled.h3`
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

export default () => {
    const t = useTranslation();
    const { clearFlashes, addFlash } = useFlash();
    const [balance, setBalance] = useState(0);
    const [prebots, setPrebots] = useState<Prebot[]>([]);
    const [loading, setLoading] = useState(true);
    const [buying, setBuying] = useState<number | null>(null);
    const [names, setNames] = useState<Record<number, string>>({});
    const { clearAndAddHttpError } = useFlashKey('prebots');

    useEffect(() => {
        getPrebots()
            .then(({ balance, prebots }) => {
                setBalance(balance);
                setPrebots(prebots);
            })
            .catch((error) => clearAndAddHttpError(error))
            .finally(() => setLoading(false));
    }, []);

    const deploy = (prebot: Prebot) => {
        const name = (names[prebot.id] || '').trim() || `${prebot.name} #1`;
        clearFlashes('prebots');
        setBuying(prebot.id);
        purchasePrebot(prebot.id, name)
            .then(({ balance, message }) => {
                setBalance(balance);
                addFlash({ key: 'prebots', type: 'success', title: t.prebots.deployed, message });
            })
            .catch((error) => clearAndAddHttpError(error))
            .finally(() => setBuying(null));
    };

    return (
        <PageContentBlock title={t.prebots.title}>
            <div className={'flex flex-wrap items-center justify-between gap-4'}>
                <div>
                    <h1 className={'text-3xl font-black tracking-wide'}>
                        <FontAwesomeIcon icon={faRobot} className={'mr-3 text-[var(--aurex-400)]'} />
                        {t.prebots.title}
                    </h1>
                    <p className={'text-neutral-400 mt-1'}>{t.prebots.subtitle}</p>
                </div>
                <div className={'flex items-center gap-2 text-xl font-bold'}>
                    <FontAwesomeIcon icon={faCoins} className={'text-[var(--aurex-400)]'} />
                    <span>{balance.toLocaleString()}</span>
                </div>
            </div>

            <ContentBox className={'mt-6 !border-[var(--aurex-500)]/30'}>
                <div className={'flex items-start gap-3'}>
                    <FontAwesomeIcon icon={faQrcode} className={'text-2xl text-[var(--aurex-400)] mt-1'} />
                    <p className={'text-sm text-neutral-300'}>{t.prebots.howItWorks}</p>
                </div>
            </ContentBox>

            {loading ? (
                <Spinner centered />
            ) : prebots.length === 0 ? (
                <ContentBox className={'mt-6 text-center text-neutral-400'}>{t.prebots.empty}</ContentBox>
            ) : (
                <PrebotGrid>
                    {prebots.map((prebot) => (
                        <PrebotCard key={prebot.id}>
                            {prebot.featured && (
                                <FeaturedBadge>
                                    <FontAwesomeIcon icon={faCrown} className={'mr-1'} />
                                    {t.prebots.featured}
                                </FeaturedBadge>
                            )}
                            <div className={'p-6 flex flex-col flex-1'}>
                                <PrebotIcon>{prebot.icon}</PrebotIcon>
                                <PrebotName>{prebot.name}</PrebotName>
                                <p className={'text-sm text-neutral-400 mt-2 flex-1'}>{prebot.description}</p>

                                <SpecGrid>
                                    <SpecCell>
                                        <FontAwesomeIcon icon={faMemory} className={'text-[var(--aurex-400)]'} />
                                        <div className={'text-sm font-bold mt-1'}>{prebot.memory} MB</div>
                                        <div className={'text-xs text-neutral-500'}>RAM</div>
                                    </SpecCell>
                                    <SpecCell>
                                        <FontAwesomeIcon icon={faHdd} className={'text-[var(--aurex-400)]'} />
                                        <div className={'text-sm font-bold mt-1'}>{(prebot.disk / 1024).toFixed(1)} GB</div>
                                        <div className={'text-xs text-neutral-500'}>Disk</div>
                                    </SpecCell>
                                    <SpecCell>
                                        <FontAwesomeIcon icon={faMicrochip} className={'text-[var(--aurex-400)]'} />
                                        <div className={'text-sm font-bold mt-1'}>{prebot.cpu}%</div>
                                        <div className={'text-xs text-neutral-500'}>CPU</div>
                                    </SpecCell>
                                </SpecGrid>

                                <div className={'mt-4 flex items-center justify-between'}>
                                    <div className={'flex items-center gap-2 text-xl font-black'}>
                                        <FontAwesomeIcon icon={faCoins} className={'text-[var(--aurex-400)]'} />
                                        {prebot.price_coins.toLocaleString()}
                                    </div>
                                </div>

                                <Input
                                    className={'mt-3'}
                                    placeholder={t.prebots.botNamePlaceholder}
                                    value={names[prebot.id] || ''}
                                    onChange={(e) => setNames({ ...names, [prebot.id]: e.target.value })}
                                />

                                <Button
                                    className={'mt-3 w-full'}
                                    disabled={buying === prebot.id || balance < prebot.price_coins}
                                    onClick={() => deploy(prebot)}
                                >
                                    {buying === prebot.id ? (
                                        <Spinner size={'small'} />
                                    ) : (
                                        <>
                                            <FontAwesomeIcon icon={faRocket} className={'mr-2'} />
                                            {t.prebots.deploy}
                                        </>
                                    )}
                                </Button>
                            </div>
                        </PrebotCard>
                    ))}
                </PrebotGrid>
            )}
        </PageContentBlock>
    );
};
