import { useStoreState } from 'easy-peasy';
import { getDictionary, Dictionary } from '@/lang';

/**
 * Returns the translation dictionary matching the logged-in user's language
 * preference (user.language: 'en' | 'es'). Falls back to English.
 * Re-renders automatically when the user changes their language.
 */
export const useTranslation = (): Dictionary => {
    const language = useStoreState((state) => state.user.data?.language);
    return getDictionary(language || undefined);
};

export default useTranslation;
