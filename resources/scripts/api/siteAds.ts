import http from '@/api/http';

export const getSiteAdBanner = (): Promise<{ banner_html: string | null }> =>
    http.get('/api/client/site-ads/banner').then(({ data }) => data);
