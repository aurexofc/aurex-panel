import http from '@/api/http';

export default (): Promise<Record<string, string>> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/account/languages')
            .then(({ data }) => resolve(data.data))
            .catch(reject);
    });
};
