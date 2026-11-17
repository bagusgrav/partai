import Link from "next/link";
import { MessageCircleQuestion } from "lucide-react";

export default function PublicQuestionButton() {
  return (
    <Link
      className="fixed bottom-5 right-5 z-40 inline-flex min-h-12 items-center gap-2 rounded-full bg-slate-950 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-950/20 transition hover:bg-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950 sm:bottom-7 sm:right-7"
      href="/tanya-jawab"
    >
      <MessageCircleQuestion aria-hidden="true" className="size-5 shrink-0" />
      <span>Ada pertanyaan? Tanya di sini.</span>
    </Link>
  );
}
