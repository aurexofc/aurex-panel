import React from 'react';
import FlashMessageRender from '@/components/FlashMessageRender';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import tw from 'twin.macro';

type Props = Readonly<
    React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement> & {
        title?: string;
        borderColor?: string;
        showFlashes?: string | boolean;
        showLoadingOverlay?: boolean;
    }
>;

const ContentBox = ({ title, borderColor, showFlashes, showLoadingOverlay, children, ...props }: Props) => (
    <div {...props}>
        {title && (
            <h2 css={tw`text-panel-text mb-4 px-4 text-2xl font-bold`}>
                {title}
                <span
                    css={tw`block w-14 h-1 mt-2 rounded-full`}
                    style={{
                        background:
                            'linear-gradient(90deg, rgb(var(--aurex-400)), rgb(var(--aurex-600) / 0.15))',
                        boxShadow: '0 0 12px rgb(var(--aurex-500) / 0.5)',
                    }}
                />
            </h2>
        )}
        {showFlashes && (
            <FlashMessageRender byKey={typeof showFlashes === 'string' ? showFlashes : undefined} css={tw`mb-4`} />
        )}
        <div css={[tw`bg-panel-surface p-4 rounded shadow-lg relative`, !!borderColor && tw`border-t-4`]}>
            <SpinnerOverlay visible={showLoadingOverlay || false} />
            {children}
        </div>
    </div>
);

export default ContentBox;
