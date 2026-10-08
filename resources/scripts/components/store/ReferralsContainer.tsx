import React, { useEffect, useState } from 'react';
import ContentBox from '@/components/elements/ContentBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import { useFlashKey } from '@/plugins/useFlash';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCopy, faGift } from '@fortawesome/free-solid-svg-icons';
import { getReferrals, claimReferral, ReferralInfo } from '@/api/referrals';
import useTranslation from '@/plugins/useTranslation';

interface Props {
    onClaimed: () => void;
}

export default ({ onClaimed }: Props) => {
    const t = useTranslation();
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('referrals');
    const { addFlash } = useFlash();
    const [info, setInfo] = useState<ReferralInfo | null>(null);
    const [code, setCode] = useState('');
    const [busy, setBusy] = useState(false);
    const [copied, setCopied] = useState(false);

    const refresh = () => {
        getReferrals()
            .then(setInfo)
            .catch(clearAndAddHttpError);
    };

    useEffect(() => {
        refresh();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const copyCode = () => {
        if (!info?.code) return;
        navigator.clipboard.writeText(info.code).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    const claim = () => {
        if (!code.trim()) return;
        setBusy(true);
        clearFlashes();
        claimReferral(code.trim().toUpperCase())
            .then((result) => {
                setCode('');
                addFlash({
                    key: 'referrals',
                    type: 'success',
                    title: t.referrals.bonusClaimed,
                    message: t.referrals.bonusAdded(result.bonus),
                });
                onClaimed();
                refresh();
            })
            .catch(clearAndAddHttpError)
            .then(() => setBusy(false));
    };

    return (
        <ContentBox title={t.referrals.title} showFlashes={'referrals'} css={tw`mt-8`}>
            {!info ? (
                <Spinner centered />
            ) : !info.enabled ? (
                <p css={tw`text-panel-text-dim text-sm`}>{t.referrals.disabled}</p>
            ) : (
                <div>
                    <p css={tw`text-panel-text text-sm mb-3`}>
                        {t.referrals.sharePrefix}{' '}
                        <span css={tw`text-primary-300 font-bold`}>{info.referrer_bonus} {t.referrals.coinsWord}</span>{' '}
                        {t.referrals.shareMiddle}{' '}
                        <span css={tw`text-primary-300 font-bold`}>{info.referred_bonus} {t.referrals.coinsWord}</span>{' '}
                        {t.referrals.shareSuffix}
                    </p>
                    <div css={tw`flex items-center gap-3 flex-wrap mb-6`}>
                        <code css={tw`text-2xl font-bold tracking-widest text-primary-300 bg-black px-4 py-2 rounded border border-primary-500/40`}>
                            {info.code}
                        </code>
                        <Button isSecondary onClick={copyCode}>
                            <FontAwesomeIcon icon={faCopy} css={tw`mr-2`} />
                            {copied ? t.referrals.copied : t.referrals.copyCode}
                        </Button>
                    </div>

                    {!info.already_claimed ? (
                        <div css={tw`mb-6`}>
                            <p css={tw`text-panel-text text-sm mb-2`}>
                                <FontAwesomeIcon icon={faGift} css={tw`mr-2 text-primary-400`} />
                                {t.referrals.haveCode}
                            </p>
                            <div css={tw`flex gap-3 items-end flex-wrap`}>
                                <div css={tw`w-48`}>
                                    <Input
                                        placeholder={t.referrals.codePlaceholder}
                                        value={code}
                                        maxLength={8}
                                        onChange={(e) => setCode(e.target.value.toUpperCase())}
                                    />
                                </div>
                                <Button color={'primary'} disabled={busy || !code.trim()} onClick={claim}>
                                    {busy ? t.referrals.claiming : t.referrals.claimBonus}
                                </Button>
                            </div>
                        </div>
                    ) : (
                        <p css={tw`text-panel-text-dim text-sm mb-6`}>{t.referrals.alreadyClaimed}</p>
                    )}

                    {info.invited.length > 0 && (
                        <div>
                            <p css={tw`text-xs uppercase font-bold text-panel-text-dim mb-2`}>
                                {t.referrals.friendsInvited(info.total_invited)}
                            </p>
                            <div css={tw`divide-y`} style={{ borderColor: 'rgb(var(--aurex-border) / 0.4)' }}>
                                {info.invited.map((r, i) => (
                                    <div key={i} css={tw`flex justify-between py-2 text-sm`}>
                                        <span css={tw`text-panel-text`}>{r.username}</span>
                                        <span css={tw`text-panel-text-dim text-xs`}>
                                            {new Date(r.rewarded_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </ContentBox>
    );
};
