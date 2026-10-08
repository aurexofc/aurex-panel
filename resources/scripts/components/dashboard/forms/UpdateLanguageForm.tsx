import React, { useEffect, useState } from 'react';
import { Actions, State, useStoreActions, useStoreState } from 'easy-peasy';
import { Form, Formik, FormikHelpers } from 'formik';
import * as Yup from 'yup';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import { httpErrorToHuman } from '@/api/http';
import { ApplicationStore } from '@/state';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import getLanguages from '@/api/account/getLanguages';
import updateLanguage from '@/api/account/updateLanguage';
import Select from '@/components/elements/Select';
import useTranslation from '@/plugins/useTranslation';

interface Values {
    language: string;
}

const schema = Yup.object().shape({
    language: Yup.string().required(),
});

export default () => {
    const t = useTranslation();
    const user = useStoreState((state: State<ApplicationStore>) => state.user.data);
    const updateUserData = useStoreActions((state: Actions<ApplicationStore>) => state.user.updateUserData);
    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const [languages, setLanguages] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        getLanguages()
            .then(setLanguages)
            .catch(() => setLanguages({ en: 'English' }))
            .then(() => setLoading(false));
    }, []);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('account:language');

        updateLanguage(values.language)
            .then(() => {
                updateUserData({ language: values.language });
                addFlash({
                    type: 'success',
                    key: 'account:language',
                    message: t.account.languageUpdated,
                });
            })
            .catch((error) =>
                addFlash({
                    type: 'error',
                    key: 'account:language',
                    title: t.account.error,
                    message: httpErrorToHuman(error),
                })
            )
            .then(() => setSubmitting(false));
    };

    if (loading) {
        return null;
    }

    return (
        <Formik onSubmit={submit} validationSchema={schema} initialValues={{ language: user!.language || 'en' }}>
            {({ isSubmitting, isValid, values, setFieldValue }) => (
                <React.Fragment>
                    <SpinnerOverlay size={'large'} visible={isSubmitting} />
                    <Form css={tw`m-0`}>
                        <div>
                            <label css={tw`block text-xs font-semibold uppercase tracking-wide text-neutral-400 mb-2`}>
                                {t.account.languageLabel}
                            </label>
                            <Select
                                value={values.language}
                                onChange={(e) => setFieldValue('language', e.target.value)}
                            >
                                {Object.entries(languages).map(([code, name]) => (
                                    <option key={code} value={code}>
                                        {name}
                                    </option>
                                ))}
                            </Select>
                        </div>
                        <div css={tw`mt-6`}>
                            <Button disabled={isSubmitting || !isValid}>{t.account.updateLanguage}</Button>
                        </div>
                    </Form>
                </React.Fragment>
            )}
        </Formik>
    );
};
