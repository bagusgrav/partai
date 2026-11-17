import type { Metadata } from "next";
import GlossaryPage from "@/components/features/glossary/GlossaryPage";

export const metadata: Metadata = {
  title: "Glosarium | PARTAI",
  description:
    "Penjelasan sederhana istilah data publik, ekonomi, partisipasi, dan tata kelola.",
};

export default function GlossaryRoute() {
  return <GlossaryPage />;
}
