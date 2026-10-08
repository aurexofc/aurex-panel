import React from 'react';
import Modal from '@/components/elements/Modal';
import Button from '@/components/elements/Button';
import tw from 'twin.macro';
import styled, { keyframes } from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCoins, faGift } from '@fortawesome/free-solid-svg-icons';

interface Props {
    coins: number;
    visible: boolean;
    onDismissed: () => void;
}

const floatSparkle = keyframes`
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.7; }
    50% { transform: translateY(-10px) scale(1.25); opacity: 1; }
`;

const coinSpin = keyframes`
    0%, 100% { transform: rotateY(-18deg); }
    50% { transform: rotateY(18deg); }
`;

const Card = styled.div`
    ${tw`relative rounded-2xl px-8 py-10 text-center overflow-hidden`};
    background: linear-gradient(165deg, #161c33 0%, #0b0f22 55%, #060913 100%);
    border: 1px solid rgba(216, 178, 74, 0.55);
    box-shadow: 0 0 80px rgba(216, 178, 74, 0.25), 0 30px 60px rgba(0, 0, 0, 0.7);
`;

const GoldTitle = styled.h2`
    ${tw`text-3xl font-semibold tracking-wide mt-2 mb-1`};
    background: linear-gradient(180deg, #f6e27a 0%, #d8b24a 55%, #a87e1f 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
`;

const CoinBadge = styled.div`
    ${tw`mx-auto flex items-center justify-center rounded-full`};
    width: 96px;
    height: 96px;
    background: radial-gradient(circle at 35% 30%, #f6e27a 0%, #d8b24a 45%, #8a6414 100%);
    box-shadow: 0 0 40px rgba(216, 178, 74, 0.65), inset 0 0 0 3px rgba(255, 255, 255, 0.25);
    animation: ${coinSpin} 2.4s ease-in-out infinite;
`;

const Sparkle = styled.span<{ left: string; top: string; delay: string }>`
    position: absolute;
    left: ${(props) => props.left};
    top: ${(props) => props.top};
    animation: ${floatSparkle} 2.2s ease-in-out infinite;
    animation-delay: ${(props) => props.delay};
    color: rgba(246, 226, 122, 0.8);
    font-size: 18px;
    pointer-events: none;
`;

const WelcomeBonusModal = ({ coins, visible, onDismissed }: Props) => (
    <Modal visible={visible} onDismissed={onDismissed} closeOnBackground={true}>
        <Card>
            <Sparkle left={'12%'} top={'18%'} delay={'0s'}>
                ✨
            </Sparkle>
            <Sparkle left={'85%'} top={'24%'} delay={'0.6s'}>
                ✨
            </Sparkle>
            <Sparkle left={'78%'} top={'68%'} delay={'1.1s'}>
                ✨
            </Sparkle>
            <Sparkle left={'10%'} top={'62%'} delay={'1.6s'}>
                ✨
            </Sparkle>
            <img
                src={'/assets/aurex-logo.png'}
                alt={'Aurex'}
                draggable={false}
                css={tw`block w-40 mx-auto select-none`}
                style={{ mixBlendMode: 'screen' }}
            />
            <GoldTitle>🎉 Congratulations!</GoldTitle>
            <p css={tw`text-neutral-300 text-sm mt-1 mb-6`}>Welcome to Aurex — here's a VIP gift for joining us.</p>
            <CoinBadge>
                <FontAwesomeIcon icon={faCoins} css={tw`text-4xl text-yellow-900`} />
            </CoinBadge>
            <p css={tw`mt-5 text-4xl font-bold`} style={{ color: '#f6e27a', textShadow: '0 0 24px rgba(216,178,74,0.6)' }}>
                +{coins.toLocaleString()}
            </p>
            <p css={tw`text-neutral-400 text-xs uppercase tracking-widest mt-1 mb-6`}>Welcome Coins</p>
            <p css={tw`text-neutral-400 text-sm mb-6`}>
                <FontAwesomeIcon icon={faGift} css={tw`mr-1 text-yellow-500`} />
                Watch ads, invite friends &amp; deploy your first server!
            </p>
            <Button onClick={onDismissed} size={'xlarge'} css={tw`w-full`}>
                Let&apos;s Go!
            </Button>
        </Card>
    </Modal>
);

export default WelcomeBonusModal;
