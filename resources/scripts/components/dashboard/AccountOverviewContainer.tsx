import * as React from 'react';
import ContentBox from '@/components/elements/ContentBox';
import UpdatePasswordForm from '@/components/dashboard/forms/UpdatePasswordForm';
import UpdateEmailAddressForm from '@/components/dashboard/forms/UpdateEmailAddressForm';
import UpdatePhoneForm from '@/components/dashboard/forms/UpdatePhoneForm';
import UpdateLanguageForm from '@/components/dashboard/forms/UpdateLanguageForm';
import UpdateThemeForm from '@/components/dashboard/forms/UpdateThemeForm';
import ConfigureTwoFactorForm from '@/components/dashboard/forms/ConfigureTwoFactorForm';
import PageContentBlock from '@/components/elements/PageContentBlock';
import tw from 'twin.macro';
import { breakpoint } from '@/theme';
import styled from 'styled-components/macro';
import MessageBox from '@/components/MessageBox';
import { useLocation } from 'react-router-dom';
import useTranslation from '@/plugins/useTranslation';

const Container = styled.div`
    ${tw`flex flex-wrap`};

    & > div {
        ${tw`w-full`};

        ${breakpoint('sm')`
      width: calc(50% - 1rem);
    `}

        ${breakpoint('md')`
      ${tw`w-auto flex-1`};
    `}
    }
`;

export default () => {
    const t = useTranslation();
    const { state } = useLocation<undefined | { twoFactorRedirect?: boolean }>();

    return (
        <PageContentBlock title={t.account.title}>
            {state?.twoFactorRedirect && (
                <MessageBox title={t.account.twoFactorRequired} type={'error'}>
                    {t.account.twoFactorMessage}
                </MessageBox>
            )}

            <Container css={[tw`lg:grid lg:grid-cols-3 mb-10`, state?.twoFactorRedirect ? tw`mt-4` : tw`mt-10`]}>
                <ContentBox title={t.account.updatePassword} showFlashes={'account:password'}>
                    <UpdatePasswordForm />
                </ContentBox>
                <ContentBox css={tw`mt-8 sm:mt-0 sm:ml-8`} title={t.account.updateEmail} showFlashes={'account:email'}>
                    <UpdateEmailAddressForm />
                </ContentBox>
                <ContentBox css={tw`md:ml-8 mt-8 md:mt-0`} title={t.account.twoStep}>
                    <ConfigureTwoFactorForm />
                </ContentBox>
            </Container>
            <Container css={[tw`lg:grid lg:grid-cols-3 mb-10`]}>
                <ContentBox title={'WhatsApp Number'} showFlashes={'account:phone'}>
                    <UpdatePhoneForm />
                </ContentBox>
                <ContentBox
                    css={tw`md:ml-8 mt-8 md:mt-0`}
                    title={t.account.language}
                    showFlashes={'account:language'}
                >
                    <UpdateLanguageForm />
                </ContentBox>
                <ContentBox css={tw`md:ml-8 mt-8 md:mt-0`} title={t.account.theme}>
                    <UpdateThemeForm />
                </ContentBox>
            </Container>
        </PageContentBlock>
    );
};
