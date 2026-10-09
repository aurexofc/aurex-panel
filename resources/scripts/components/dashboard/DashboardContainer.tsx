import React, { useEffect, useState } from 'react';
import { Server } from '@/api/server/getServer';
import getServers from '@/api/getServers';
import ServerRow from '@/components/dashboard/ServerRow';
import Spinner from '@/components/elements/Spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import AurexWidgets from '@/components/dashboard/AurexWidgets';
import SiteAdBanner from '@/components/elements/SiteAdBanner';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from 'easy-peasy';
import { usePersistedState } from '@/plugins/usePersistedState';
import Switch from '@/components/elements/Switch';
import tw from 'twin.macro';
import useSWR from 'swr';
import { PaginatedResult } from '@/api/http';
import Pagination from '@/components/elements/Pagination';
import WelcomeBonusModal from '@/components/dashboard/WelcomeBonusModal';
import Particles from '@/components/dashboard/Particles';
import useTranslation from '@/plugins/useTranslation';
import { useLocation } from 'react-router-dom';
import { Link } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faServer, faRocket } from '@fortawesome/free-solid-svg-icons';
import styled from 'styled-components/macro';

const SectionTitle = styled.h2`
    ${tw`flex items-center gap-3 text-sm font-extrabold uppercase tracking-[0.2em] text-panel-text mt-8 mb-4`};
    &::before {
        content: '';
        ${tw`inline-block rounded-full`};
        width: 2rem;
        height: 3px;
        background: linear-gradient(90deg, #d8b24a, transparent);
    }
`;

const EmptyState = styled.div`
    ${tw`rounded-xl p-10 text-center`};
    background: linear-gradient(150deg, rgb(var(--aurex-surface)) 0%, rgb(var(--aurex-bg)) 70%);
    border: 1px dashed rgba(216, 178, 74, 0.35);
`;

export default () => {
    const t = useTranslation();
    const { search } = useLocation();
    const defaultPage = Number(new URLSearchParams(search).get('page') || '1');

    const [page, setPage] = useState(!isNaN(defaultPage) && defaultPage > 0 ? defaultPage : 1);
    const [welcomeBonus, setWelcomeBonus] = useState<number | null>(null);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [showOnlyAdmin, setShowOnlyAdmin] = usePersistedState(`${uuid}:show_all_servers`, false);

    const { data: servers, error } = useSWR<PaginatedResult<Server>>(
        ['/api/client/servers', showOnlyAdmin && rootAdmin, page],
        () => getServers({ page, type: showOnlyAdmin && rootAdmin ? 'admin' : undefined })
    );

    useEffect(() => {
        setPage(1);
    }, [showOnlyAdmin]);

    useEffect(() => {
        if (!servers) return;
        if (servers.pagination.currentPage > 1 && !servers.items.length) {
            setPage(1);
        }
    }, [servers?.pagination.currentPage]);

    useEffect(() => {
        // Don't use react-router to handle changing this part of the URL, otherwise it
        // triggers a needless re-render. We just want to track this in the URL incase the
        // user refreshes the page.
        window.history.replaceState(null, document.title, `/${page <= 1 ? '' : `?page=${page}`}`);
    }, [page]);

    useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'dashboard', error });
        if (!error) clearFlashes('dashboard');
    }, [error]);

    useEffect(() => {
        // Show the VIP welcome gift popup once, right after a fresh registration.
        const bonus = sessionStorage.getItem('aurex_welcome_bonus');
        if (bonus) {
            sessionStorage.removeItem('aurex_welcome_bonus');
            const amount = parseInt(bonus, 10);
            if (!isNaN(amount) && amount > 0) {
                setWelcomeBonus(amount);
            }
        }
    }, []);

    return (
        <PageContentBlock title={t.dashboard.title} showFlashKey={'dashboard'}>
            {/* Animated layer: floating theme-colored particles drift behind the dashboard content. */}
            <div css={tw`relative`}>
                <Particles count={24} />
                <div css={tw`relative`}>
                    <SiteAdBanner />
                    <div className={'aurex-fade-up'}>
                        <AurexWidgets />
                    </div>
                    {rootAdmin && (
                        <div css={tw`mb-2 flex justify-end items-center`} className={'aurex-fade-up'} style={{ animationDelay: '80ms' }}>
                            <p css={tw`uppercase text-xs text-panel-text-dim mr-2`}>
                                {showOnlyAdmin ? t.dashboard.showingOthers : t.dashboard.showingYours}
                            </p>
                            <Switch
                                name={'show_all_servers'}
                                defaultChecked={showOnlyAdmin}
                                onChange={() => setShowOnlyAdmin((s) => !s)}
                            />
                        </div>
                    )}
                    {!servers ? (
                        <Spinner centered size={'large'} />
                    ) : (
                        <Pagination data={servers} onPageSelect={setPage}>
                            {({ items }) =>
                                items.length > 0 ? (
                                    <>
                                        <div className={'aurex-fade-up'} style={{ animationDelay: '120ms' }}>
                                            <SectionTitle>{t.dashboard.yourServers}</SectionTitle>
                                        </div>
                                        {items.map((server, index) => (
                                            <div
                                                key={server.uuid}
                                                className={'aurex-fade-up'}
                                                style={{ animationDelay: `${160 + index * 70}ms` }}
                                            >
                                                <ServerRow
                                                    server={server}
                                                    css={index > 0 ? tw`mt-3` : undefined}
                                                />
                                            </div>
                                        ))}
                                    </>
                                ) : (
                                    <div className={'aurex-fade-up'} style={{ animationDelay: '120ms' }}>
                                        <EmptyState>
                                            <FontAwesomeIcon icon={faServer} size={'3x'} css={tw`text-primary-400/60`} />
                                            <p css={tw`text-lg font-bold text-panel-text mt-4`}>
                                                {showOnlyAdmin ? t.dashboard.noOtherServers : t.dashboard.noServersYet}
                                            </p>
                                            {!showOnlyAdmin && (
                                                <>
                                                    <p css={tw`text-sm text-panel-text-dim mt-2 mb-6`}>
                                                        {t.dashboard.emptyHint}
                                                    </p>
                                                    <Link
                                                        to={'/store'}
                                                        css={tw`inline-flex items-center gap-2 rounded-lg px-6 py-3 font-bold text-sm no-underline transition-all duration-200 hover:-translate-y-0.5`}
                                                        style={{
                                                            background:
                                                                'linear-gradient(180deg, #f6e27a 0%, #d8b24a 60%, #b98f2b 100%)',
                                                            color: '#1a1405',
                                                            boxShadow: '0 6px 24px rgba(216, 178, 74, 0.35)',
                                                        }}
                                                    >
                                                        <FontAwesomeIcon icon={faRocket} />
                                                        {t.dashboard.visitStore}
                                                    </Link>
                                                </>
                                            )}
                                        </EmptyState>
                                    </div>
                                )
                            }
                        </Pagination>
                    )}
                </div>
            </div>
            {welcomeBonus !== null && (
                <WelcomeBonusModal coins={welcomeBonus} visible={true} onDismissed={() => setWelcomeBonus(null)} />
            )}
        </PageContentBlock>
    );
};
