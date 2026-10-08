import http from '@/api/http';

export interface RegisterResponse {
    complete: boolean;
    intended?: string;
    welcome_bonus?: number;
}

export interface RegisterData {
    username: string;
    email: string;
    phone?: string;
    password: string;
    password_confirmation: string;
    recaptchaData?: string | null;
}

export default ({ username, email, phone, password, password_confirmation, recaptchaData }: RegisterData): Promise<RegisterResponse> => {
    return new Promise((resolve, reject) => {
        http.get('/sanctum/csrf-cookie')
            .then(() =>
                http.post('/auth/register', {
                    username,
                    email,
                    phone,
                    password,
                    password_confirmation,
                    'g-recaptcha-response': recaptchaData,
                })
            )
            .then(({ data }) => resolve(data.data || data))
            .catch(reject);
    });
};
