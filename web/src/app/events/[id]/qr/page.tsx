import { Suspense } from "react";
import EventQrClientPage from "./EventQrClientPage";

interface EventQrPageProps {
  params: Promise<{ id: string }>;
}

async function EventQrPageContent({ params }: EventQrPageProps) {
  const { id } = await params;

  return <EventQrClientPage eventId={id} />;
}

export default function EventQrPage(props: EventQrPageProps) {
  return (
    <Suspense fallback={<p className="p-8 text-center font-semibold text-slate-600">Memuat QR acara...</p>}>
      <EventQrPageContent {...props} />
    </Suspense>
  );
}
