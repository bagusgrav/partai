import { Suspense } from "react";
import ClaimAttendanceClientPage from "./ClaimAttendanceClientPage";

interface ClaimAttendancePageProps {
  params: Promise<{ id: string }>;
}

async function ClaimAttendancePageContent({ params }: ClaimAttendancePageProps) {
  const { id } = await params;

  return <ClaimAttendanceClientPage eventId={id} />;
}

export default function ClaimAttendancePage(props: ClaimAttendancePageProps) {
  return (
    <Suspense fallback={<p className="p-8 text-center font-semibold text-slate-600">Memuat halaman klaim...</p>}>
      <ClaimAttendancePageContent {...props} />
    </Suspense>
  );
}
