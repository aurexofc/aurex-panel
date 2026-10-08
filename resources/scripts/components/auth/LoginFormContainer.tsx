import React, { forwardRef } from 'react';
import { Form } from 'formik';
import styled from 'styled-components/macro';
import { breakpoint } from '@/theme';
import FlashMessageRender from '@/components/FlashMessageRender';
import tw from 'twin.macro';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

const Container = styled.div`
    ${tw`mx-auto`};

    ${breakpoint('sm')`
        ${tw`w-4/5`}
    `};

    ${breakpoint('md')`
        ${tw`p-10`}
    `};

    ${breakpoint('lg')`
        ${tw`w-3/5`}
    `};

    ${breakpoint('xl')`
        ${tw`w-full`}
        max-width: 700px;
    `};
`;

const VipCard = styled.div`
    ${tw`relative w-full rounded-2xl px-6 py-8 md:px-10 md:py-10 mx-1 overflow-hidden`};
    background: linear-gradient(165deg, #161c33 0%, #0b0f22 55%, #060913 100%);
    border: 1px solid rgba(216, 178, 74, 0.4);
    box-shadow: 0 0 70px rgba(216, 178, 74, 0.14), 0 30px 60px rgba(0, 0, 0, 0.65);
`;

const GoldTitle = styled.h2`
    ${tw`text-2xl md:text-3xl text-center font-semibold tracking-wide pt-2 pb-6`};
    background: linear-gradient(180deg, #f6e27a 0%, #d8b24a 55%, #a87e1f 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
`;

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => (
    <Container>
        <FlashMessageRender css={tw`mb-2 px-1`} />
        <Form {...props} ref={ref}>
            <VipCard>
                <img
                    src={'/assets/aurex-logo.png'}
                    alt={'Aurex'}
                    draggable={false}
                    css={tw`block w-64 md:w-80 mx-auto select-none`}
                />
                {title && <GoldTitle>{title}</GoldTitle>}
                <div css={tw`mt-2`}>{props.children}</div>
            </VipCard>
        </Form>
        <p css={tw`text-center text-neutral-500 text-xs mt-4`}>
            &copy; 2015 - {new Date().getFullYear()}&nbsp;
            <a
                rel={'noopener nofollow noreferrer'}
                href={'#'}
                target={'_blank'}
                css={tw`no-underline text-neutral-500 hover:text-primary-400`}
            >
                Aurex Software
            </a>
        </p>
    </Container>
));
