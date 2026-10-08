import http from '@/api/http';

export interface Announcement {
    id: number;
    title: string;
    body: string;
    created_at: string;
}

export const getAnnouncements = (): Promise<Announcement[]> =>
    http.get('/api/client/announcements').then(({ data }) => data.announcements || []);
