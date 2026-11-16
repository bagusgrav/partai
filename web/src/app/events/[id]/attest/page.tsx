import { Suspense } from "react";
import AttestAttendanceClientPage from "./AttestAttendanceClientPage";

interface AttestAttendancePageProps {
  params: Promise<{ id: string }>;
}

async function AttestAttendancePageContent({ params }: AttestAttendancePageProps) {
  const { id } = await params;

  return <AttestAttendanceClientPage eventId={id} />;
}

export default function AttestAttendancePage(props: AttestAttendancePageProps) {
  return (
    <Suspense fallback={<p className="p-8 text-center font-semibold text-slate-600">Memuat klaim...</p>}>
      <AttestAttendancePageContent {...props} />
    </Suspense>
  );
}
