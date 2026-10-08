import en from './en';
import es from './es';

const dictionaries = { en, es } as const;

export type Dictionary = typeof en;

export const getDictionary = (code?: string | null): Dictionary =>
    (code && (dictionaries as Record<string, Dictionary>)[code]) || dictionaries.en;
