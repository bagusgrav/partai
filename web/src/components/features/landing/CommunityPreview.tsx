"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { getPublicPosts, type CommunityPost } from "@/lib/api/posts";

const relativeTimeFormatter = new Intl.RelativeTimeFormat("id-ID", {
  numeric: "auto",
});

const timeUnits: [Intl.RelativeTimeFormatUnit, number][] = [
  ["year", 365 * 24 * 60 * 60],
  ["month", 30 * 24 * 60 * 60],
  ["week", 7 * 24 * 60 * 60],
  ["day", 24 * 60 * 60],
  ["hour", 60 * 60],
  ["minute", 60],
  ["second", 1],
];

function formatRelativeTime(date: string): string {
  const difference = (new Date(date).getTime() - Date.now()) / 1000;
  const [unit, secondsPerUnit] =
    timeUnits.find(([, seconds]) => Math.abs(difference) >= seconds) ??
    timeUnits[timeUnits.length - 1];

  return relativeTimeFormatter.format(
    Math.round(difference / secondsPerUnit),
    unit,
  );
}

function truncatePost(body: string): string {
  const characters = Array.from(body);
  return characters.length > 150
    ? `${characters.slice(0, 149).join("").trimEnd()}…`
    : body;
}

export default function CommunityPreview() {
  const [posts, setPosts] = useState<CommunityPost[] | null>(null);

  useEffect(() => {
    let active = true;

    getPublicPosts()
      .then(({ data }) => {
        if (active) {
          setPosts(
            data
              .filter((post) => post.visibility === "public")
              .sort(
                (first, second) =>
                  new Date(second.created_at).getTime() -
                  new Date(first.created_at).getTime(),
              )
              .slice(0, 3),
          );
        }
      })
      .catch((error: unknown) => {
        console.error("Gagal memuat preview komunitas publik.", error);
        if (active) {
          setPosts([]);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  if (!posts?.length) {
    return null;
  }

  return (
    <section
      aria-labelledby="community-preview-title"
      className="mx-auto w-full max-w-7xl px-6 py-16 sm:px-8 sm:py-20"
    >
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p className="text-sm font-black uppercase tracking-[0.2em] text-primary-hover">
            Ruang komunitas
          </p>
          <h2
            className="mt-3 text-2xl font-black tracking-tight text-text-main sm:text-3xl"
            id="community-preview-title"
          >
            Kabar Warga
          </h2>
        </div>
        <Link
          className="inline-flex min-h-11 items-center justify-center self-start rounded-md border border-primary/20 px-4 py-2.5 font-bold text-primary-hover transition hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 sm:self-auto"
          href="/komunitas"
        >
          Lihat Semua Diskusi
          <span aria-hidden="true" className="ml-2">→</span>
        </Link>
      </div>

      <div className="mt-7 grid gap-4 md:grid-cols-3">
        {posts.map((post) => (
          <article
            className="rounded-2xl border border-background bg-surface p-5 shadow-sm"
            key={post.id}
          >
            <p className="font-bold text-text-main">{post.user.name}</p>
            <p className="mt-3 whitespace-pre-wrap break-words leading-6 text-text-muted">
              {truncatePost(post.body)}
            </p>
            <time
              className="mt-4 block text-sm text-text-muted"
              dateTime={post.created_at}
            >
              {formatRelativeTime(post.created_at)}
            </time>
          </article>
        ))}
      </div>
    </section>
  );
}
