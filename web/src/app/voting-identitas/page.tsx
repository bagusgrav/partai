import type { Metadata } from "next";
import IdentityProposalPage from "@/components/features/governance/IdentityProposalPage";

export const metadata: Metadata = {
  title: "Usulan Identitas Partai | PARTAI",
  description: "Usulkan nama, warna utama, dan deskripsi logo partai.",
};

export default function IdentityVotingPage() {
  return <IdentityProposalPage />;
}
