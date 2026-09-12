import type { FormEvent } from 'react';

type AuthFormProps = { title: string; onSubmit?: (event: FormEvent<HTMLFormElement>) => void };

export function AuthForm({ title, onSubmit }: AuthFormProps) {
  return <main className="grid min-h-screen place-items-center bg-slate-950 p-6 text-slate-100"><section className="w-full max-w-md rounded-xl bg-slate-900 p-8 shadow-2xl"><h1 className="text-2xl font-semibold">{title}</h1><form onSubmit={onSubmit} className="mt-6 space-y-4"><label className="block text-sm">Email or username<input className="mt-1 w-full rounded border border-slate-700 bg-slate-800 p-2" autoComplete="username" /></label><label className="block text-sm">Password<input type="password" className="mt-1 w-full rounded border border-slate-700 bg-slate-800 p-2" autoComplete="current-password" /></label><button className="w-full rounded bg-indigo-600 p-2 font-medium">Continue</button></form></section></main>;
}

export const LoginPage = () => <AuthForm title="Welcome to Flow" />;
export const ForgotPasswordPage = () => <AuthForm title="Reset your password" />;
export const ResetPasswordPage = () => <AuthForm title="Choose a new password" />;
export const VerifyEmailPage = () => <AuthForm title="Verify your email" />;
export const ChangePasswordPage = () => <AuthForm title="Change password" />;
export const ProfilePage = () => <section><h1>Profile</h1><p>Personal information and preferences.</p></section>;
export const SecuritySettingsPage = () => <section><h1>Security Center</h1><p>MFA, password, trusted devices, and security alerts.</p></section>;
export const MfaSetupPage = () => <section><h1>Multi-factor authentication</h1><p>Enroll an authenticator and save recovery codes.</p></section>;
export const SessionsPage = () => <section><h1>Active sessions</h1><p>Review, identify, and revoke signed-in devices.</p></section>;
export const LoginHistoryPage = () => <section><h1>Login history</h1><p>Review successful and blocked login activity.</p></section>;
