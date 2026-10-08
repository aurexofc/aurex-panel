import React from 'react';
import { Route, Switch, useRouteMatch } from 'react-router-dom';
import styled from 'styled-components/macro';
import tw from 'twin.macro';
import LoginContainer from '@/components/auth/LoginContainer';
import RegisterContainer from '@/components/auth/RegisterContainer';
import ForgotPasswordContainer from '@/components/auth/ForgotPasswordContainer';
import ResetPasswordContainer from '@/components/auth/ResetPasswordContainer';
import LoginCheckpointContainer from '@/components/auth/LoginCheckpointContainer';
import { NotFound } from '@/components/elements/ScreenBlock';
import { useHistory, useLocation } from 'react-router';

const Page = styled.div`
    ${tw`min-h-screen flex relative overflow-hidden`};
    background:
        radial-gradient(ellipse 55% 40% at 50% 36%, rgba(216, 178, 74, 0.1), transparent 70%),
        linear-gradient(180deg, #0a0e1c 0%, #060913 100%);
`;

export default () => {
    const history = useHistory();
    const location = useLocation();
    const { path } = useRouteMatch();

    return (
        <Page>
            <div css={tw`m-auto w-full py-10`}>
                <Switch location={location}>
                    <Route path={`${path}/login`} component={LoginContainer} exact />
                    <Route path={`${path}/register`} component={RegisterContainer} exact />
                    <Route path={`${path}/login/checkpoint`} component={LoginCheckpointContainer} />
                    <Route path={`${path}/password`} component={ForgotPasswordContainer} exact />
                    <Route path={`${path}/password/reset/:token`} component={ResetPasswordContainer} />
                    <Route path={`${path}/checkpoint`} />
                    <Route path={'*'}>
                        <NotFound onBack={() => history.push('/auth/login')} />
                    </Route>
                </Switch>
            </div>
        </Page>
    );
};
