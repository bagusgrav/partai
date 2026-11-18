import type { RegisteredUser } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

const GUEST_TOKEN_KEY = "partai_guest_token";
const GUEST_REQUEST_ID_KEY = "partai_guest_request_id";
export const AUTH_STATE_CHANGED_EVENT = "partai:auth-state-changed";
export const SSO_URL = "/masuk";
let guestTokenPromise: Promise<string> | null = null;

function saveSessionToken(token: string): void {
  localStorage.setItem(GUEST_TOKEN_KEY, token);
  localStorage.removeItem(GUEST_REQUEST_ID_KEY);
  window.dispatchEvent(new Event(AUTH_STATE_CHANGED_EVENT));
}

function clearSessionToken(): void {
  localStorage.removeItem(GUEST_TOKEN_KEY);
  localStorage.removeItem(GUEST_REQUEST_ID_KEY);
  window.dispatchEvent(new Event(AUTH_STATE_CHANGED_EVENT));
}

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

    const activeToken = localStorage.getItem(GUEST_TOKEN_KEY);
    if (activeToken) {
      return activeToken;
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

  saveSessionToken(payload.token);
}

export async function loginMember(input: {
  email: string;
  password: string;
}): Promise<void> {
  const response = await fetchWithRetry(`${API_BASE_URL}/auth/login`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify(input),
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
    throw new Error("Respons masuk tidak berisi token valid.");
  }

  saveSessionToken(payload.token);
}

export async function logoutMember(): Promise<void> {
  const token = localStorage.getItem(GUEST_TOKEN_KEY);

  try {
    if (token) {
      const response = await fetchWithRetry(`${API_BASE_URL}/auth/logout`, {
        method: "POST",
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
      });

      await ensureSuccessfulResponse(response);
    }
  } finally {
    clearSessionToken();
  }
}

export async function forgotPassword(email: string): Promise<void> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/auth/forgot-password`,
    {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email, request_id: crypto.randomUUID() }),
    },
  );

  await ensureSuccessfulResponse(response);
}

export async function resetPassword(input: {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}): Promise<void> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/auth/reset-password`,
    {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ ...input, request_id: crypto.randomUUID() }),
    },
  );

  await ensureSuccessfulResponse(response);
}

export async function getCurrentUser(): Promise<RegisteredUser> {
  const response = await fetchWithRetry(`${API_BASE_URL}/user`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
