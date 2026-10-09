import React, { useEffect, useRef, useState } from 'react';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import { getSiteAdBanner } from '@/api/siteAds';

const BannerWrap = styled.div`
    ${tw`rounded-xl overflow-hidden my-4 flex justify-center`};
    min-height: 90px;
`;

/**
 * Renders the admin-configured Adsterra/Monetag banner for free users.
 * Premium users (and disabled ads) get null from the API — nothing renders.
 * Scripts inside the ad HTML are executed by re-inserting them as live nodes.
 */
export default () => {
    const [html, setHtml] = useState<string | null>(null);
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        getSiteAdBanner()
            .then(({ banner_html }) => setHtml(banner_html))
            .catch(() => setHtml(null));
    }, []);

    useEffect(() => {
        if (!html || !ref.current) return;
        ref.current.innerHTML = html;
        // Re-insert <script> tags so ad network code actually executes.
        ref.current.querySelectorAll('script').forEach((oldScript) => {
            const script = document.createElement('script');
            Array.from(oldScript.attributes).forEach((attr) =>
                script.setAttribute(attr.name, attr.value)
            );
            script.textContent = oldScript.textContent;
            oldScript.replaceWith(script);
        });
    }, [html]);

    if (!html) return null;

    return <BannerWrap ref={ref} />;
};
