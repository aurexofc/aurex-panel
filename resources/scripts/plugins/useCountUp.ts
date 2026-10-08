import { useEffect, useRef, useState } from 'react';

/**
 * Count-up animation for stat numbers.
 *
 * Counts from 0 to `target` over `duration` ms with an ease-out cubic.
 * Returns the target immediately for users who prefer reduced motion,
 * and returns null until a non-null target arrives.
 */
export const useCountUp = (target: number | null, duration = 900): number | null => {
    const [value, setValue] = useState<number | null>(null);
    const raf = useRef<number>(0);

    useEffect(() => {
        if (target === null || target === undefined) {
            setValue(null);
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setValue(target);
            return;
        }

        const start = performance.now();
        const tick = (now: number): void => {
            const t = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - t, 3);
            setValue(Math.round(target * eased));
            if (t < 1) raf.current = requestAnimationFrame(tick);
        };

        raf.current = requestAnimationFrame(tick);
        return () => cancelAnimationFrame(raf.current);
    }, [target, duration]);

    return value;
};

export default useCountUp;
