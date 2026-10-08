import React, { Suspense } from 'react';
import styled, { keyframes } from 'styled-components/macro';
import tw from 'twin.macro';
import ErrorBoundary from '@/components/elements/ErrorBoundary';

export type SpinnerSize = 'small' | 'base' | 'large';

interface Props {
    size?: SpinnerSize;
    centered?: boolean;
    isBlue?: boolean;
}

interface Spinner extends React.FC<Props> {
    Size: Record<'SMALL' | 'BASE' | 'LARGE', SpinnerSize>;
    Suspense: React.FC<Props>;
}

const spin = keyframes`
    to { transform: rotate(360deg); }
`;

const spinReverse = keyframes`
    to { transform: rotate(-360deg); }
`;

const logoPulse = keyframes`
    0%, 100% {
        transform: scale(1);
        filter: drop-shadow(0 0 3px rgba(216, 178, 74, 0.45));
    }
    50% {
        transform: scale(1.08);
        filter: drop-shadow(0 0 10px rgba(216, 178, 74, 0.95));
    }
`;

// Aurex VIP spinner — the crown logo breathing with a golden glow in the
// center, wrapped by a thin rotating gold arc. Small spinners (buttons,
// inline) keep a compact gold ring since the logo would be unreadable.
const SpinnerComponent = styled.div<Props>`
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    ${(props) => (props.size === 'small' ? tw`w-4 h-4` : props.size === 'large' ? tw`w-16 h-16` : tw`w-8 h-8`)};

    & > .aurex-track {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        border-style: solid;
        border-width: ${(props) => (props.size === 'small' ? '2px' : '2px')};
        border-color: ${(props) => (props.isBlue ? 'hsla(212, 92%, 43%, 0.18)' : 'rgba(216, 178, 74, 0.22)')};
    }

    & > .aurex-arc {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        border-style: solid;
        border-width: 2px;
        border-color: transparent;
        border-top-color: ${(props) => (props.isBlue ? 'hsl(212, 92%, 62%)' : '#f6e27a')};
        border-right-color: ${(props) => (props.isBlue ? 'hsla(212, 92%, 43%, 0.55)' : 'rgba(216, 178, 74, 0.55)')};
        animation: ${spin} 0.9s cubic-bezier(0.55, 0.25, 0.25, 0.7) infinite;
        filter: ${(props) =>
            props.isBlue
                ? 'drop-shadow(0 0 5px hsla(212, 92%, 55%, 0.8))'
                : 'drop-shadow(0 0 6px rgba(216, 178, 74, 0.8))'};
    }

    & > .aurex-arc-inner {
        position: absolute;
        border-radius: 9999px;
        border-style: solid;
        border-width: 2px;
        border-color: transparent;
        border-bottom-color: ${(props) => (props.isBlue ? 'hsla(212, 92%, 62%, 0.55)' : 'rgba(246, 226, 122, 0.5)')};
        border-left-color: ${(props) => (props.isBlue ? 'hsla(212, 92%, 43%, 0.25)' : 'rgba(216, 178, 74, 0.25)')};
        animation: ${spinReverse} 1.5s linear infinite;
        ${tw`inset-1`};
    }

    & > .aurex-logo {
        width: 72%;
        height: 72%;
        object-fit: contain;
        mix-blend-mode: screen;
        animation: ${logoPulse} 1.8s ease-in-out infinite;
        user-select: none;
        pointer-events: none;
    }
`;

const Spinner: Spinner = ({ centered, size = 'base', ...props }) => {
    const spinner =
        size === 'small' ? (
            <SpinnerComponent size={size} {...props}>
                <div className="aurex-track" />
                <div className="aurex-arc" />
                <div className="aurex-arc-inner" />
            </SpinnerComponent>
        ) : (
            <SpinnerComponent size={size} {...props}>
                <div className="aurex-track" />
                <div className="aurex-arc" />
                <img src={'/assets/aurex-logo.png'} className="aurex-logo" alt={''} draggable={false} />
            </SpinnerComponent>
        );

    return centered ? (
        <div css={[tw`flex justify-center items-center`, size === 'large' ? tw`m-20` : tw`m-6`]}>{spinner}</div>
    ) : (
        spinner
    );
};
Spinner.displayName = 'Spinner';

Spinner.Size = {
    SMALL: 'small',
    BASE: 'base',
    LARGE: 'large',
};

Spinner.Suspense = ({ children, centered = true, size = Spinner.Size.LARGE, ...props }) => (
    <Suspense fallback={<Spinner centered={centered} size={size} {...props} />}>
        <ErrorBoundary>{children}</ErrorBoundary>
    </Suspense>
);
Spinner.Suspense.displayName = 'Spinner.Suspense';

export default Spinner;
