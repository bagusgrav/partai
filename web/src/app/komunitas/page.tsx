import type { Metadata } from "next";
import CommunityPage from "@/components/features/community/CommunityPage";

export const metadata: Metadata = {
  title: "Komunitas | PARTAI",
  description: "Feed teks komunitas PARTAI.",
};

export default function KomunitasRoute() {
  return <CommunityPage />;
}
