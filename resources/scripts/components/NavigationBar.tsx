import * as React from 'react';
import { useState } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCogs, faLayerGroup, faSignOutAlt, faStore, faRobot, faTicketAlt, faCrown } from '@fortawesome/free-solid-svg-icons';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import SearchContainer from '@/components/dashboard/search/SearchContainer';
import tw, { theme } from 'twin.macro';
import styled from 'styled-components/macro';
import http from '@/api/http';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import Avatar from '@/components/Avatar';
import useTranslation from '@/plugins/useTranslation';

const RightNavigation = styled.div`
    & > a,
    & > button,
    & > .navigation-link {
        ${tw`flex items-center h-full no-underline text-neutral-300 px-6 cursor-pointer transition-all duration-150`};

        &:active,
        &:hover {
            ${tw`text-neutral-100 bg-black`};
        }

        &:active,
        &:hover,
        &.active {
            box-shadow: inset 0 -2px ${theme`colors.cyan.600`.toString()};
        }
    }
`;

export default () => {
    const t = useTranslation();
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data!.rootAdmin);
    const username = useStoreState((state: ApplicationStore) => state.user.data!.username);
    const isPremium = useStoreState((state: ApplicationStore) => state.user.data!.isPremium);
    const [isLoggingOut, setIsLoggingOut] = useState(false);

    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    return (
        <div className={'w-full bg-neutral-900 shadow-md overflow-x-auto'}>
            <SpinnerOverlay visible={isLoggingOut} />
            <div className={'mx-auto w-full flex items-center h-[3.5rem] max-w-[1200px]'}>
                <div id={'logo'} className={'flex-1 flex items-center gap-2'}>
                    <Link
                        to={'/'}
                        className={
                            'text-2xl font-header font-medium px-4 no-underline text-neutral-200 hover:text-neutral-100 transition-colors duration-150'
                        }
                    >
                        {name}
                    </Link>
                    <span className={'hidden sm:flex items-center gap-1.5 text-sm text-neutral-400'}>
                        <span className={'font-medium text-neutral-300'}>{username}</span>
                        {isPremium ? (
                            <span
                                className={'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider'}
                                style={{
                                    background: 'linear-gradient(115deg, rgb(var(--aurex-300)), rgb(var(--aurex-500)))',
                                    color: 'rgb(var(--aurex-bg))',
                                }}
                            >
                                👑 Premium
                            </span>
                        ) : (
                            <span
                                className={'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider bg-neutral-700 text-neutral-300'}
                            >
                                Free
                            </span>
                        )}
                    </span>
                </div>
                <RightNavigation className={'flex h-full items-center justify-center'}>
                    <SearchContainer />
                    <Tooltip placement={'bottom'} content={t.nav.dashboard}>
                        <NavLink to={'/'} exact>
                            <FontAwesomeIcon icon={faLayerGroup} />
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t.nav.store}>
                        <NavLink to={'/store'}>
                            <FontAwesomeIcon icon={faStore} />
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t.nav.prebots}>
                        <NavLink to={'/prebots'}>
                            <FontAwesomeIcon icon={faRobot} />
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t.nav.redeem}>
                        <NavLink to={'/redeem'}>
                            <FontAwesomeIcon icon={faTicketAlt} />
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t.nav.premium}>
                        <NavLink to={'/premium'}>
                            <FontAwesomeIcon icon={faCrown} />
                        </NavLink>
                    </Tooltip>
                    {rootAdmin && (
                        <Tooltip placement={'bottom'} content={t.nav.admin}>
                            <a href={'/admin'} rel={'noreferrer'}>
                                <FontAwesomeIcon icon={faCogs} />
                            </a>
                        </Tooltip>
                    )}
                    <Tooltip placement={'bottom'} content={t.nav.accountSettings}>
                        <NavLink to={'/account'}>
                            <span className={'flex items-center w-5 h-5'}>
                                <Avatar.User />
                            </span>
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t.nav.signOut}>
                        <button onClick={onTriggerLogout}>
                            <FontAwesomeIcon icon={faSignOutAlt} />
                        </button>
                    </Tooltip>
                </RightNavigation>
            </div>
        </div>
    );
};
