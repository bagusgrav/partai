import type { RegisteredUser } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

const GUEST_TOKEN_KEY = "partai_guest_token";
const GUEST_REQUEST_ID_KEY = "partai_guest_request_id";
let guestTokenPromise: Promise<string> | null = null;

export async function getHeaders(json = false): Promise<Headers> {
  const headers = new Headers({ Accept: "application/json" });
  headers.set("Authorization", `Bearer ${await getGuestToken()}`);

  if (json) {
    headers.set("Content-Type", "application/json");
  }

  return headers;
}

export async function getGuestToken(): Promise<string> {
  if (typeof window === "undefined") {
    throw new Error("Sesi tamu hanya dapat dibuat di browser.");
  }

  const cachedToken = localStorage.getItem(GUEST_TOKEN_KEY);
  if (cachedToken) {
    return cachedToken;
  }

  const tokenPromise = (guestTokenPromise ??= (async () => {
    const requestId =
      localStorage.getItem(GUEST_REQUEST_ID_KEY) ?? crypto.randomUUID();
    localStorage.setItem(GUEST_REQUEST_ID_KEY, requestId);

    const response = await fetchWithRetry(`${API_BASE_URL}/auth/guest`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ request_id: requestId }),
    });

    await ensureSuccessfulResponse(response);

    const payload: unknown = await response.json();
    if (
      typeof payload !== "object" ||
      payload === null ||
      !("token" in payload) ||
      typeof payload.token !== "string" ||
      payload.token.length === 0
    ) {
      throw new Error("Respons pembuatan sesi tamu tidak berisi token valid.");
    }

    localStorage.setItem(GUEST_TOKEN_KEY, payload.token);
    localStorage.removeItem(GUEST_REQUEST_ID_KEY);

    return payload.token;
  })());

  try {
    return await tokenPromise;
  } finally {
    if (guestTokenPromise === tokenPromise) {
      guestTokenPromise = null;
    }
  }
}

export async function registerMember(
  input: {
    name: string;
    email: string;
    password: string;
    cluster_id: string;
    nik?: string;
  },
  requestId: string,
): Promise<void> {
  const response = await fetchWithRetry(`${API_BASE_URL}/auth/register`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ...input, request_id: requestId }),
  });

  await ensureSuccessfulResponse(response);

  const payload: unknown = await response.json();
  if (
    typeof payload !== "object" ||
    payload === null ||
    !("token" in payload) ||
    typeof payload.token !== "string" ||
    payload.token.length === 0
  ) {
    throw new Error("Respons pendaftaran tidak berisi token valid.");
  }

  localStorage.setItem(GUEST_TOKEN_KEY, payload.token);
  localStorage.removeItem(GUEST_REQUEST_ID_KEY);
}

export async function getCurrentUser(): Promise<RegisteredUser> {
  const response = await fetchWithRetry(`${API_BASE_URL}/user`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
