import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Partai | Belajar Kewargaan",
  description: "Belajar kewargaan dengan sesi kuis singkat.",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="id" className="h-full antialiased">
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
