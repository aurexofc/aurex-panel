import React, { useEffect } from 'react';
import ContentContainer from '@/components/elements/ContentContainer';
import { CSSTransition } from 'react-transition-group';
import tw from 'twin.macro';
import styled, { keyframes } from 'styled-components/macro';
import FlashMessageRender from '@/components/FlashMessageRender';

export interface PageContentBlockProps {
    title?: string;
    className?: string;
    showFlashKey?: string;
}

const marquee = keyframes`
    0%, 100% { transform: translateX(-25%); }
    50% { transform: translateX(25%); }
`;

const VipMarquee = styled.div`
    overflow: hidden;
    white-space: nowrap;
    text-align: center;
    margin-top: 10px;

    & > span {
        display: inline-block;
        animation: ${marquee} 6s ease-in-out infinite;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        background: linear-gradient(90deg, #d8b24a, #f5d67b, #d8b24a);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;

        @media (prefers-reduced-motion: reduce) {
            animation: none;
        }
    }
`;

const PageContentBlock: React.FC<PageContentBlockProps> = ({ title, showFlashKey, className, children }) => {
    useEffect(() => {
        if (title) {
            document.title = title;
        }
    }, [title]);

    return (
        <CSSTransition timeout={150} classNames={'fade'} appear in>
            <>
                <ContentContainer css={tw`my-4 sm:my-10`} className={className}>
                    {showFlashKey && <FlashMessageRender byKey={showFlashKey} css={tw`mb-4`} />}
                    {children}
                </ContentContainer>
                <ContentContainer css={tw`mb-4`}>
                    <p css={tw`text-center text-neutral-500 text-xs`}>
                        <a
                            rel={'noopener nofollow noreferrer'}
                            href={'#'}
                            target={'_blank'}
                            css={tw`no-underline text-neutral-500 hover:text-neutral-300`}
                        >
                            Aurex
                        </a>
                        &nbsp;&copy; 2015 - {new Date().getFullYear()}
                    </p>
                    <VipMarquee>
                        <span>VIP Host Owner &mdash; Asif Ofc</span>
                    </VipMarquee>
                </ContentContainer>
            </>
        </CSSTransition>
    );
};

export default PageContentBlock;
