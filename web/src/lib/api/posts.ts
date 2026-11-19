import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export type PostVisibility = "public" | "members";

export interface CommunityPost {
  id: string;
  body: string;
  visibility: PostVisibility;
  created_at: string;
  edited_at: string | null;
  user: {
    name: string;
  };
}

export interface PostsFeed {
  data: CommunityPost[];
  links: {
    next: string | null;
  };
}

export interface CreatePostInput {
  request_id: string;
  body: string;
  visibility: PostVisibility;
}

function parsePost(value: unknown): CommunityPost {
  if (
    typeof value !== "object" ||
    value === null ||
    !("id" in value) ||
    typeof value.id !== "string" ||
    !("body" in value) ||
    typeof value.body !== "string" ||
    !("visibility" in value) ||
    (value.visibility !== "public" && value.visibility !== "members") ||
    !("created_at" in value) ||
    typeof value.created_at !== "string" ||
    !("edited_at" in value) ||
    (value.edited_at !== null && typeof value.edited_at !== "string") ||
    !("user" in value) ||
    typeof value.user !== "object" ||
    value.user === null ||
    !("name" in value.user) ||
    typeof value.user.name !== "string"
  ) {
    throw new Error("Respons post tidak valid.");
  }

  return {
    id: value.id,
    body: value.body,
    visibility: value.visibility,
    created_at: value.created_at,
    edited_at: value.edited_at,
    user: {
      name: value.user.name,
    },
  };
}

function parseFeed(value: unknown): PostsFeed {
  if (
    typeof value !== "object" ||
    value === null ||
    !("data" in value) ||
    !Array.isArray(value.data) ||
    !("links" in value) ||
    typeof value.links !== "object" ||
    value.links === null ||
    !("next" in value.links) ||
    (value.links.next !== null && typeof value.links.next !== "string")
  ) {
    throw new Error("Respons feed post tidak valid.");
  }

  return {
    data: value.data.map(parsePost),
    links: { next: value.links.next },
  };
}

async function getFeed(
  path: string,
  cursor?: string,
  authenticated = false,
): Promise<PostsFeed> {
  const query = new URLSearchParams();
  if (cursor) {
    query.set("cursor", cursor);
  }
  const suffix = query.size > 0 ? `?${query.toString()}` : "";
  const response = await fetchWithRetry(`${API_BASE_URL}${path}${suffix}`, {
    method: "GET",
    headers: authenticated
      ? await getHeaders()
      : new Headers({ Accept: "application/json" }),
    ...(authenticated ? { cache: "no-store" } : {}),
  });

  await ensureSuccessfulResponse(response);
  return parseFeed(await response.json());
}

export function getPublicPosts(cursor?: string): Promise<PostsFeed> {
  return getFeed("/posts", cursor);
}

export function getMemberPosts(cursor?: string): Promise<PostsFeed> {
  return getFeed("/posts/members", cursor, true);
}

export async function createPost(input: CreatePostInput): Promise<CommunityPost> {
  const response = await fetchWithRetry(`${API_BASE_URL}/posts`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify(input),
  });

  await ensureSuccessfulResponse(response);
  const result: unknown = await response.json();

  if (
    typeof result !== "object" ||
    result === null ||
    !("data" in result) ||
    typeof result.data !== "object" ||
    result.data === null
  ) {
    throw new Error("Respons pembuatan post tidak valid.");
  }

  return parsePost(result.data);
}

export async function updatePost(
  postId: string,
  input: Partial<Pick<CreatePostInput, "body" | "visibility">>,
): Promise<CommunityPost> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/posts/${encodeURIComponent(postId)}`,
    {
      method: "PATCH",
      headers: await getHeaders(true),
      body: JSON.stringify(input),
    },
  );

  await ensureSuccessfulResponse(response);
  return parsePostFromUpdate(await response.json());
}

async function parsePostFromUpdate(value: unknown): Promise<CommunityPost> {
  if (typeof value !== "object" || value === null || !("data" in value)) {
    throw new Error("Respons pembaruan post tidak valid.");
  }

  return parsePost(value.data);
}

export async function deletePost(postId: string): Promise<void> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/posts/${encodeURIComponent(postId)}`,
    {
      method: "DELETE",
      headers: await getHeaders(),
    },
  );

  await ensureSuccessfulResponse(response);
}

export async function reportPost(postId: string, reason: string): Promise<void> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/posts/${encodeURIComponent(postId)}/report`,
    {
      method: "POST",
      headers: await getHeaders(true),
      body: JSON.stringify({ reason }),
    },
  );

  await ensureSuccessfulResponse(response);
}
