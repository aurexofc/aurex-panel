import React, { useMemo } from 'react';
import tw from 'twin.macro';

/**
 * Subtle floating particles for the dashboard background.
 *
 * Pure CSS animation (transform/opacity only, GPU-friendly). Particle color
 * resolves to the active theme via --aurex-400/--aurex-500. Positions and
 * timings use a seeded PRNG so they are stable across re-renders.
 * Hidden automatically when the user prefers reduced motion.
 */

// Deterministic PRNG so particles don't jump between renders.
const mulberry32 = (seed: number) => (): number => {
    seed |= 0;
    seed = (seed + 0x6d2b79f5) | 0;
    let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
};

interface ParticleSpec {
    left: number;
    size: number;
    duration: number;
    delay: number;
    dx: number;
    opacity: number;
}

export default ({ count = 24 }: { count?: number }) => {
    const particles = useMemo<ParticleSpec[]>(() => {
        const rand = mulberry32(20261007);
        return Array.from({ length: count }, () => ({
            left: rand() * 100, // % across the container
            size: 3 + rand() * 4, // 3–7px (soft radial glow makes them look smaller)
            duration: 10 + rand() * 12, // 10–22s per rise
            delay: -rand() * 22, // negative: spread through the cycle on first paint
            dx: (rand() - 0.5) * 90, // horizontal drift in px
            opacity: 0.1 + rand() * 0.16, // 0.10–0.26
        }));
    }, [count]);

    return (
        <div css={tw`pointer-events-none absolute inset-0 overflow-hidden`} aria-hidden={'true'}>
            {particles.map((p, i) => (
                <span
                    key={i}
                    className={'aurex-particle'}
                    style={
                        {
                            left: `${p.left}%`,
                            width: `${p.size}px`,
                            height: `${p.size}px`,
                            animationDuration: `${p.duration}s`,
                            animationDelay: `${p.delay}s`,
                            '--aurex-particle-dx': `${p.dx}px`,
                            '--aurex-particle-opacity': p.opacity,
                        } as React.CSSProperties
                    }
                />
            ))}
        </div>
    );
};
