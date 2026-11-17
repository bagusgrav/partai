import { Suspense } from "react";
import ResetPasswordForm from "@/components/features/auth/ResetPasswordForm";

export default function ResetPasswordPage() {
  return (
    <Suspense
      fallback={
        <main className="flex-1 px-5 py-10 sm:px-8 sm:py-14">
          <section className="mx-auto max-w-xl rounded-3xl border border-slate-200 bg-surface p-8 text-center text-text-muted shadow-xl shadow-primary/5">
            Memuat formulir reset password...
          </section>
        </main>
      }
    >
      <ResetPasswordForm />
    </Suspense>
  );
}
