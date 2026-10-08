import http from '@/api/http';

export default (phone: string | null): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.put('/api/client/account/phone', { phone })
            .then(() => resolve())
            .catch(reject);
    });
};
