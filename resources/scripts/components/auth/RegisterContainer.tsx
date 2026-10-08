import React, { useEffect, useRef, useState } from 'react';
import { Link, RouteComponentProps } from 'react-router-dom';
import register from '@/api/auth/register';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string, ref } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';

interface Values {
    username: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
}

const RegisterContainer = ({ history }: RouteComponentProps) => {
    const recaptchaRef = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);

    useEffect(() => {
        clearFlashes();
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        if (recaptchaEnabled && !token) {
            recaptchaRef.current!.execute().catch((error) => {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });

            return;
        }

        register({ ...values, recaptchaData: token })
            .then((response) => {
                // Stash the welcome bonus so the dashboard can celebrate it once.
                if (response.welcome_bonus && response.welcome_bonus > 0) {
                    sessionStorage.setItem('aurex_welcome_bonus', String(response.welcome_bonus));
                }
                // @ts-expect-error this is valid
                window.location = response.intended || '/';
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (recaptchaRef.current) recaptchaRef.current.reset();

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ username: '', email: '', phone: '', password: '', password_confirmation: '' }}
            validationSchema={object().shape({
                username: string()
                    .required('Please choose a username.')
                    .min(3, 'Username must be at least 3 characters.')
                    .max(32, 'Username is too long.'),
                email: string().required('An email address is required.').email('Please enter a valid email address.'),
                phone: string()
                    .max(20, 'Phone number is too long.')
                    .matches(/^[+\d][\d\s-]*$/, 'Please enter a valid phone number.')
                    .nullable(),
                password: string().required('Please choose a password.').min(8, 'Password must be at least 8 characters.'),
                password_confirmation: string()
                    .required('Please confirm your password.')
                    .oneOf([ref('password')], 'Passwords do not match.'),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={'Create your Account'} css={tw`w-full flex`}>
                    <Field type={'text'} label={'Username'} name={'username'} disabled={isSubmitting} />
                    <div css={tw`mt-6`}>
                        <Field type={'email'} label={'Email'} name={'email'} disabled={isSubmitting} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            type={'text'}
                            label={'WhatsApp Number (optional)'}
                            name={'phone'}
                            description={'Get welcome bonus & password reset alerts on WhatsApp.'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field type={'password'} label={'Password'} name={'password'} disabled={isSubmitting} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            type={'password'}
                            label={'Confirm Password'}
                            name={'password_confirmation'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} isLoading={isSubmitting} disabled={isSubmitting}>
                            Create Account
                        </Button>
                    </div>
                    {recaptchaEnabled && (
                        <Reaptcha
                            ref={recaptchaRef}
                            size={'invisible'}
                            sitekey={siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                setToken(response);
                                submitForm();
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                setToken('');
                            }}
                        />
                    )}
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/login'}
                            css={tw`text-xs text-neutral-400 tracking-wide no-underline uppercase hover:text-primary-400`}
                        >
                            Already have an account? Login
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default RegisterContainer;
