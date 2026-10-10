import React, { useEffect, useState } from 'react';
import { Button } from '@/components/elements/button/index';
import Can from '@/components/elements/Can';
import { ServerContext } from '@/state/server';
import { PowerAction } from '@/components/server/console/ServerConsoleContainer';
import { Dialog } from '@/components/elements/dialog';
import styled from 'styled-components/macro';
import tw from 'twin.macro';

const GoldButtonWrap = styled.div`
    & button {
        ${tw`font-extrabold uppercase tracking-wider text-xs px-5 py-2.5 rounded-xl transition-all duration-200`};
        background: linear-gradient(180deg, #f6e27a 0%, #d8b24a 60%, #b98f2b 100%) !important;
        color: #1a1405 !important;
        border: 1px solid rgba(246, 226, 122, 0.5) !important;
        box-shadow: 0 4px 18px rgba(216, 178, 74, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;

        &:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(216, 178, 74, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
        }

        &:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
    }
`;

const GhostButtonWrap = styled.div`
    & button {
        ${tw`font-bold uppercase tracking-wider text-xs px-5 py-2.5 rounded-xl transition-all duration-200`};
        background: rgb(var(--aurex-surface-2)) !important;
        color: rgb(var(--aurex-text)) !important;
        border: 1px solid rgb(var(--aurex-border)) !important;

        &:hover:not(:disabled) {
            border-color: rgb(var(--aurex-400) / 0.5) !important;
            transform: translateY(-1px);
        }

        &:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
    }
`;

const DangerButtonWrap = styled.div`
    & button {
        ${tw`font-extrabold uppercase tracking-wider text-xs px-5 py-2.5 rounded-xl transition-all duration-200`};
        background: linear-gradient(180deg, rgba(239, 68, 68, 0.18), rgba(239, 68, 68, 0.08)) !important;
        color: #fca5a5 !important;
        border: 1px solid rgba(239, 68, 68, 0.45) !important;
        box-shadow: 0 4px 16px rgba(239, 68, 68, 0.15) !important;

        &:hover:not(:disabled) {
            background: linear-gradient(180deg, rgba(239, 68, 68, 0.28), rgba(239, 68, 68, 0.12)) !important;
            transform: translateY(-1px);
        }

        &:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
    }
`;

interface PowerButtonProps {
    className?: string;
}

export default ({ className }: PowerButtonProps) => {
    const [open, setOpen] = useState(false);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);

    const killable = status === 'stopping';
    const onButtonClick = (
        action: PowerAction | 'kill-confirmed',
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>
    ): void => {
        e.preventDefault();
        if (action === 'kill') {
            return setOpen(true);
        }

        if (instance) {
            setOpen(false);
            instance.send('set state', action === 'kill-confirmed' ? 'kill' : action);
        }
    };

    useEffect(() => {
        if (status === 'offline') {
            setOpen(false);
        }
    }, [status]);

    return (
        <div className={className}>
            <Dialog.Confirm
                open={open}
                hideCloseIcon
                onClose={() => setOpen(false)}
                title={'Forcibly Stop Process'}
                confirm={'Continue'}
                onConfirmed={onButtonClick.bind(this, 'kill-confirmed')}
            >
                Forcibly stopping a server can lead to data corruption.
            </Dialog.Confirm>
            <Can action={'control.start'}>
                <GoldButtonWrap className={'flex-1'}>
                    <Button
                        className={'w-full'}
                        disabled={status !== 'offline'}
                        onClick={onButtonClick.bind(this, 'start')}
                    >
                        Start
                    </Button>
                </GoldButtonWrap>
            </Can>
            <Can action={'control.restart'}>
                <GhostButtonWrap className={'flex-1'}>
                    <Button.Text className={'w-full'} disabled={!status} onClick={onButtonClick.bind(this, 'restart')}>
                        Restart
                    </Button.Text>
                </GhostButtonWrap>
            </Can>
            <Can action={'control.stop'}>
                <DangerButtonWrap className={'flex-1'}>
                    <Button.Danger
                        className={'w-full'}
                        disabled={status === 'offline'}
                        onClick={onButtonClick.bind(this, killable ? 'kill' : 'stop')}
                    >
                        {killable ? 'Kill' : 'Stop'}
                    </Button.Danger>
                </DangerButtonWrap>
            </Can>
        </div>
    );
};
