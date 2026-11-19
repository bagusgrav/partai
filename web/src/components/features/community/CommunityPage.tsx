"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import {
  createPost,
  getMemberPosts,
  getPublicPosts,
  type CommunityPost,
  type CreatePostInput,
  type PostsFeed,
  type PostVisibility,
} from "@/lib/api/posts";
import { getCurrentUser } from "@/lib/api/auth";
import { API_BASE_URL } from "@/lib/api/client";
import type { RegisteredUser } from "@/types";

type FeedTab = "public" | "members";

function nextCursor(feed: PostsFeed): string | null {
  if (!feed.links.next) {
    return null;
  }

  return new URL(feed.links.next, API_BASE_URL).searchParams.get("cursor");
}

function visibilityLabel(visibility: PostVisibility): string {
  return visibility === "public" ? "Publik" : "Anggota";
}

function PostList({ posts }: { posts: CommunityPost[] }) {
  if (posts.length === 0) {
    return (
      <p className="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-600">
        Belum ada post di feed ini.
      </p>
    );
  }

  return (
    <div className="grid gap-4">
      {posts.map((post) => (
        <article
          className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
          key={post.id}
        >
          <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
            <p className="font-semibold text-slate-950">{post.user.name}</p>
            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
              {visibilityLabel(post.visibility)}
            </span>
          </div>
          <p className="whitespace-pre-wrap break-words text-sm leading-6 text-slate-800">
            {post.body}
          </p>
          <time
            className="mt-4 block text-xs text-slate-500"
            dateTime={post.created_at}
          >
            {new Date(post.created_at).toLocaleString("id-ID")}
            {post.edited_at ? " · diedit" : ""}
          </time>
        </article>
      ))}
    </div>
  );
}

export default function CommunityPage() {
  const [user, setUser] = useState<RegisteredUser | null>(null);
  const [authResolved, setAuthResolved] = useState(false);
  const [authError, setAuthError] = useState<string | null>(null);
  const [tab, setTab] = useState<FeedTab>("public");
  const [publicPosts, setPublicPosts] = useState<CommunityPost[]>([]);
  const [memberPosts, setMemberPosts] = useState<CommunityPost[]>([]);
  const [publicCursor, setPublicCursor] = useState<string | null>(null);
  const [memberCursor, setMemberCursor] = useState<string | null>(null);
  const [publicLoading, setPublicLoading] = useState(true);
  const [memberLoading, setMemberLoading] = useState(false);
  const [memberLoaded, setMemberLoaded] = useState(false);
  const [moreLoading, setMoreLoading] = useState(false);
  const [feedError, setFeedError] = useState<string | null>(null);
  const [body, setBody] = useState("");
  const [visibility, setVisibility] = useState<PostVisibility>("members");
  const [pendingCreate, setPendingCreate] = useState<CreatePostInput | null>(null);
  const [sending, setSending] = useState(false);
  const [composerMessage, setComposerMessage] = useState<string | null>(null);

  const isMember = user !== null && user.role !== "guest";
  const activePosts = tab === "public" ? publicPosts : memberPosts;
  const activeCursor = tab === "public" ? publicCursor : memberCursor;
  const activeLoading =
    tab === "public"
      ? publicLoading
      : memberLoading || (authResolved && isMember && !memberLoaded);

  useEffect(() => {
    let active = true;

    getCurrentUser()
      .then((currentUser) => {
        if (active) {
          setUser(currentUser);
          setAuthError(null);
        }
      })
      .catch((error: unknown) => {
        if (active) {
          setUser(null);
          setAuthError(
            error instanceof Error ? error.message : "Sesi tidak dapat dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setAuthResolved(true);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  const loadPublic = useCallback(async (cursor?: string) => {
    setFeedError(null);
    if (cursor) {
      setMoreLoading(true);
    } else {
      setPublicLoading(true);
    }

    try {
      const feed = await getPublicPosts(cursor);
      setPublicPosts((current) =>
        cursor ? [...current, ...feed.data] : feed.data,
      );
      setPublicCursor(nextCursor(feed));
    } catch (error: unknown) {
      setFeedError(
        error instanceof Error ? error.message : "Feed publik gagal dimuat.",
      );
    } finally {
      setPublicLoading(false);
      setMoreLoading(false);
    }
  }, []);

  useEffect(() => {
    let active = true;

    getPublicPosts()
      .then((feed) => {
        if (active) {
          setPublicPosts(feed.data);
          setPublicCursor(nextCursor(feed));
        }
      })
      .catch((error: unknown) => {
        if (active) {
          setFeedError(
            error instanceof Error ? error.message : "Feed publik gagal dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setPublicLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  const loadMembers = useCallback(async (cursor?: string) => {
    setFeedError(null);
    if (cursor) {
      setMoreLoading(true);
    } else {
      setMemberLoading(true);
    }

    try {
      const feed = await getMemberPosts(cursor);
      setMemberPosts((current) =>
        cursor ? [...current, ...feed.data] : feed.data,
      );
      setMemberCursor(nextCursor(feed));
    } catch (error: unknown) {
      setFeedError(
        error instanceof Error ? error.message : "Feed anggota gagal dimuat.",
      );
    } finally {
      setMemberLoading(false);
      setMemberLoaded(true);
      setMoreLoading(false);
    }
  }, []);

  useEffect(() => {
    if (tab !== "members" || !authResolved || !isMember || memberLoaded) {
      return;
    }

    let active = true;
    getMemberPosts()
      .then((feed) => {
        if (active) {
          setMemberPosts(feed.data);
          setMemberCursor(nextCursor(feed));
        }
      })
      .catch((error: unknown) => {
        if (active) {
          setFeedError(
            error instanceof Error ? error.message : "Feed anggota gagal dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setMemberLoaded(true);
        }
      });

    return () => {
      active = false;
    };
  }, [authResolved, isMember, memberLoaded, tab]);

  async function submitPost(): Promise<void> {
    if (sending) {
      return;
    }

    const operation =
      pendingCreate ??
      ({
        request_id: crypto.randomUUID(),
        body,
        visibility,
      } satisfies CreatePostInput);

    if (!pendingCreate && operation.body.trim().length === 0) {
      setComposerMessage("Tulis teks post terlebih dahulu.");
      return;
    }

    setPendingCreate(operation);
    setSending(true);
    setComposerMessage(null);

    try {
      const created = await createPost(operation);
      if (created.visibility === "public") {
        setPublicPosts((current) => [created, ...current]);
      } else {
        setMemberPosts((current) => [created, ...current]);
      }
      setBody("");
      setVisibility("members");
      setPendingCreate(null);
      setComposerMessage("Post berhasil dikirim.");
    } catch (error: unknown) {
      setComposerMessage(
        error instanceof Error
          ? error.message
          : "Post gagal dikirim. Coba ulangi.",
      );
    } finally {
      setSending(false);
    }
  }

  return (
    <main className="mx-auto w-full max-w-4xl flex-1 px-4 py-8 sm:px-6 lg:py-12">
      <header className="mb-8">
        <p className="text-sm font-bold uppercase tracking-[0.14em] text-slate-500">
          PARTAI
        </p>
        <h1 className="mt-2 text-3xl font-black tracking-tight text-slate-950">
          Komunitas
        </h1>
        <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
          Berbagi gagasan dalam bentuk teks. Pilih visibilitas sebelum
          menerbitkan post.
        </p>
      </header>

      <div className="mb-6 flex gap-2 border-b border-slate-200">
        {([
          ["public", "Publik"],
          ["members", "Anggota"],
        ] as const).map(([value, label]) => (
          <button
            aria-selected={tab === value}
            className={`border-b-2 px-4 py-3 text-sm font-semibold ${
              tab === value
                ? "border-slate-950 text-slate-950"
                : "border-transparent text-slate-600 hover:text-slate-950"
            }`}
            key={value}
            onClick={() => setTab(value)}
            role="tab"
            type="button"
          >
            {label}
          </button>
        ))}
      </div>

      {tab === "members" && authResolved && !isMember ? (
        <section className="rounded-xl border border-slate-200 bg-white p-6">
          <h2 className="text-lg font-bold text-slate-950">Khusus anggota</h2>
          <p className="mt-2 text-sm leading-6 text-slate-600">
            Masuk sebagai anggota untuk melihat feed dan ikut berbagi.
          </p>
          {authError && (
            <p className="mt-2 text-sm text-red-700" role="alert">
              {authError}
            </p>
          )}
          <Link
            className="mt-4 inline-flex rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
            href="/masuk"
          >
            Masuk
          </Link>
        </section>
      ) : (
        <>
          {isMember && (
            <section className="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
              <label
                className="mb-2 block text-sm font-bold text-slate-900"
                htmlFor="community-post-body"
              >
                Tulis post
              </label>
              <textarea
                className="min-h-32 w-full resize-y rounded-lg border border-slate-300 px-3 py-2.5 text-sm leading-6 text-slate-900 focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200 disabled:bg-slate-100"
                disabled={pendingCreate !== null}
                id="community-post-body"
                maxLength={500}
                onChange={(event) => setBody(event.target.value)}
                placeholder="Apa yang ingin kamu bagikan?"
                value={body}
              />
              <div className="mt-3 flex flex-wrap items-center justify-between gap-3">
                <label className="flex items-center gap-2 text-sm text-slate-700">
                  Visibilitas
                  <select
                    className="rounded-md border border-slate-300 bg-white px-2 py-1.5"
                    disabled={pendingCreate !== null}
                    onChange={(event) =>
                      setVisibility(
                        event.target.value === "public" ? "public" : "members",
                      )
                    }
                    value={visibility}
                  >
                    <option value="members">Anggota</option>
                    <option value="public">Publik</option>
                  </select>
                </label>
                <span className="text-xs text-slate-500">{body.length}/500</span>
                <button
                  className="rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                  disabled={sending || (!pendingCreate && body.trim().length === 0)}
                  onClick={() => void submitPost()}
                  type="button"
                >
                  {sending
                    ? "Mengirim..."
                    : pendingCreate
                      ? "Coba kirim ulang"
                      : "Kirim"}
                </button>
              </div>
              {composerMessage && (
                <div className="mt-3 flex flex-wrap items-center gap-3">
                  <p
                    className={`text-sm ${
                      pendingCreate ? "text-red-700" : "text-slate-600"
                    }`}
                    role={pendingCreate ? "alert" : "status"}
                  >
                    {composerMessage}
                  </p>
                  {pendingCreate && !sending && (
                    <button
                      className="text-sm font-semibold text-slate-700 underline"
                      onClick={() => {
                        setPendingCreate(null);
                        setComposerMessage(null);
                      }}
                      type="button"
                    >
                      Batalkan percobaan
                    </button>
                  )}
                </div>
              )}
            </section>
          )}

          {activeLoading && activePosts.length === 0 ? (
            <p className="py-8 text-center text-sm text-slate-600" role="status">
              Memuat post...
            </p>
          ) : (
            <PostList posts={activePosts} />
          )}
          {feedError && (
            <div className="mt-4 flex flex-wrap items-center gap-3">
              <p className="text-sm text-red-700" role="alert">
                {feedError}
              </p>
              <button
                className="text-sm font-semibold text-slate-700 underline"
                onClick={() => {
                  if (tab === "public") {
                    void loadPublic();
                  } else {
                    void loadMembers();
                  }
                }}
                type="button"
              >
                Coba lagi
              </button>
            </div>
          )}
          {activeCursor && (
            <button
              className="mt-5 w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:bg-slate-50 disabled:opacity-60"
              disabled={moreLoading}
              onClick={() =>
                void (tab === "public"
                  ? loadPublic(activeCursor)
                  : loadMembers(activeCursor))
              }
              type="button"
            >
              {moreLoading ? "Memuat..." : "Muat lebih banyak"}
            </button>
          )}
        </>
      )}
    </main>
  );
}
