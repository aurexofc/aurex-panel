import React from 'react';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import useTranslation from '@/plugins/useTranslation';
import useTheme, { THEME_KEYS, THEME_SWATCHES, ThemeKey } from '@/plugins/useTheme';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCheck } from '@fortawesome/free-solid-svg-icons';

const SwatchGrid = styled.div`
    ${tw`flex flex-wrap gap-4`};
`;

const SwatchButton = styled.button<{ $swatch: string; $active: boolean }>`
    ${tw`relative flex flex-col items-center gap-2 p-3 rounded-xl cursor-pointer transition-all duration-200 border-2 bg-transparent`};
    border-color: ${(props) => (props.$active ? props.$swatch : 'transparent')};
    min-width: 84px;

    &:hover {
        border-color: ${(props) => props.$swatch};
        transform: translateY(-2px);
    }

    &:focus {
        outline: none;
        border-color: ${(props) => props.$swatch};
    }
`;

const SwatchDot = styled.span<{ $swatch: string }>`
    width: 44px;
    height: 44px;
    border-radius: 9999px;
    background: ${(props) => props.$swatch};
    box-shadow: 0 0 16px ${(props) => props.$swatch}66;
    ${tw`flex items-center justify-center text-white`};
`;

export default () => {
    const t = useTranslation();
    const { theme, setTheme } = useTheme();

    const names: Record<ThemeKey, string> = {
        gold: t.account.themeGold,
        crimson: t.account.themeCrimson,
        ocean: t.account.themeOcean,
        emerald: t.account.themeEmerald,
        purple: t.account.themePurple,
    };

    return (
        <div>
            <p css={tw`text-sm text-neutral-400 mb-4`}>{t.account.themeDescription}</p>
            <SwatchGrid>
                {THEME_KEYS.map((key) => {
                    const active = theme === key;
                    return (
                        <SwatchButton
                            key={key}
                            type={'button'}
                            $swatch={THEME_SWATCHES[key]}
                            $active={active}
                            onClick={() => setTheme(key)}
                            aria-pressed={active}
                            title={names[key]}
                        >
                            <SwatchDot $swatch={THEME_SWATCHES[key]}>
                                {active && <FontAwesomeIcon icon={faCheck} />}
                            </SwatchDot>
                            <span css={tw`text-xs font-semibold text-neutral-300`}>{names[key]}</span>
                        </SwatchButton>
                    );
                })}
            </SwatchGrid>
        </div>
    );
};
