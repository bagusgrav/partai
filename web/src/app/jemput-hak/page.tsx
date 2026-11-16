import type { Metadata } from "next";
import RightsChecker from "@/components/features/rights/RightsChecker";

export const metadata: Metadata = {
  title: "Cek Status Bansos | PARTAI",
  description: "Periksa status bantuan sosial melalui layanan Jemput Hak.",
};

export default function JemputHakPage() {
  return <RightsChecker />;
}
