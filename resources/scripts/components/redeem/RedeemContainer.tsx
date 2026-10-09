import React, { useState } from 'react';
import PageContentBlock from '@/components/elements/PageContentBlock';
import ContentBox from '@/components/elements/ContentBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTicketAlt, faCoins, faGift } from '@fortawesome/free-solid-svg-icons';
import { claimRedeemCode } from '@/api/redeem';
import useTranslation from '@/plugins/useTranslation';

const RedeemCard = styled.div`
    ${tw`rounded-2xl relative overflow-hidden p-8 text-center`};
    background:
        linear-gradient(rgb(var(--aurex-surface-2)), rgb(var(--aurex-surface))) padding-box,
        linear-gradient(155deg, rgb(var(--aurex-300) / 0.85), rgb(var(--aurex-600) / 0.25) 38%, rgb(var(--aurex-border) / 0.6) 72%, rgb(var(--aurex-400) / 0.5)) border-box;
    border: 1px solid transparent;
    box-shadow: 0 10px 30px rgb(0 0 0 / 0.45);
    max-width: 520px;
    margin: 0 auto;
`;

const TicketIcon = styled.div`
    ${tw`text-6xl mb-4`};
    color: rgb(var(--aurex-400));
    filter: drop-shadow(0 0 18px rgb(var(--aurex-500) / 0.5));
`;

export default () => {
    const t = useTranslation();
    const { addFlash, clearFlashes } = useFlash();
    const [code, setCode] = useState('');
    const [loading, setLoading] = useState(false);
    const [claimed, setClaimed] = useState<{ coins: number } | null>(null);

    const onClaim = () => {
        if (!code.trim() || loading) return;
        setLoading(true);
        clearFlashes('redeem');
        claimRedeemCode(code.trim())
            .then((result) => {
                setClaimed({ coins: result.coins });
                setCode('');
            })
            .catch((error) => {
                addFlash({
                    type: 'error',
                    key: 'redeem',
                    message: error?.response?.data?.message || error?.message || t.redeem.failed,
                });
            })
            .finally(() => setLoading(false));
    };

    return (
        <PageContentBlock title={t.redeem.title}>
            <RedeemCard>
                <TicketIcon>
                    <FontAwesomeIcon icon={faTicketAlt} />
                </TicketIcon>
                <h2 css={tw`text-2xl font-bold mb-2`} style={{ color: 'rgb(var(--aurex-300))' }}>
                    {t.redeem.title}
                </h2>
                <p css={tw`text-sm mb-6 opacity-70`}>{t.redeem.subtitle}</p>

                {claimed ? (
                    <ContentBox css={tw`mb-4`}>
                        <div css={tw`text-5xl mb-3`}>
                            <FontAwesomeIcon icon={faGift} style={{ color: 'rgb(var(--aurex-400))' }} />
                        </div>
                        <p css={tw`text-xl font-bold`} style={{ color: 'rgb(var(--aurex-300))' }}>
                            <FontAwesomeIcon icon={faCoins} /> +{claimed.coins.toLocaleString()}
                        </p>
                        <p css={tw`text-sm mt-1 opacity-70`}>{t.redeem.success}</p>
                        <Button css={tw`mt-4`} onClick={() => setClaimed(null)}>
                            {t.redeem.claimAnother}
                        </Button>
                    </ContentBox>
                ) : (
                    <>
                        <div css={tw`flex gap-2`}>
                            <Input
                                placeholder={t.redeem.placeholder}
                                value={code}
                                onChange={(e) => setCode(e.target.value.toUpperCase())}
                                onKeyDown={(e) => e.key === 'Enter' && onClaim()}
                                css={tw`text-center uppercase tracking-widest font-mono`}
                            />
                        </div>
                        <Button
                            css={tw`mt-4 w-full`}
                            disabled={!code.trim() || loading}
                            onClick={onClaim}
                        >
                            {loading ? <Spinner size={'small'} centered /> : t.redeem.claim}
                        </Button>
                    </>
                )}
            </RedeemCard>
        </PageContentBlock>
    );
};
