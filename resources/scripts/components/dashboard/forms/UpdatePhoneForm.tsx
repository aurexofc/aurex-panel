import React from 'react';
import { Actions, State, useStoreActions, useStoreState } from 'easy-peasy';
import { Form, Formik, FormikHelpers } from 'formik';
import * as Yup from 'yup';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Field from '@/components/elements/Field';
import { httpErrorToHuman } from '@/api/http';
import { ApplicationStore } from '@/state';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';

interface Values {
    phone: string;
}

const schema = Yup.object().shape({
    phone: Yup.string()
        .max(20, 'Phone number is too long.')
        .matches(/^[+\d][\d\s-]*$/, 'Please enter a valid phone number.')
        .nullable(),
});

export default () => {
    const user = useStoreState((state: State<ApplicationStore>) => state.user.data);
    const updateUserData = useStoreActions((state: Actions<ApplicationStore>) => state.user.updateUserData);
    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('account:phone');

        // Lazy import to avoid circular deps
        import('@/api/account/updatePhone').then(({ default: updatePhone }) =>
            updatePhone(values.phone || null)
                .then(() => {
                    updateUserData({ phone: values.phone || null } as any);
                    addFlash({
                        type: 'success',
                        key: 'account:phone',
                        message: 'WhatsApp number updated. You will receive notifications here.',
                    });
                })
                .catch((error) =>
                    addFlash({
                        type: 'error',
                        key: 'account:phone',
                        title: 'Error',
                        message: httpErrorToHuman(error),
                    })
                )
                .then(() => setSubmitting(false))
        );
    };

    return (
        <Formik
            onSubmit={submit}
            validationSchema={schema}
            initialValues={{ phone: (user as any)?.phone || '' }}
        >
            {({ isSubmitting, isValid }) => (
                <React.Fragment>
                    <SpinnerOverlay size={'large'} visible={isSubmitting} />
                    <Form css={tw`m-0`}>
                        <Field
                            type={'text'}
                            name={'phone'}
                            label={'WhatsApp Number'}
                            description={'For welcome messages, password resets & top-up alerts.'}
                        />
                        <div css={tw`mt-6`}>
                            <Button disabled={isSubmitting || !isValid}>Update Number</Button>
                        </div>
                    </Form>
                </React.Fragment>
            )}
        </Formik>
    );
};
