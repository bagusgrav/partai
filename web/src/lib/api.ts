import type {
  AnswerResponse,
  ActiveVotesResponse,
  ActiveEventsResponse,
  AttendanceAttestationResult,
  AttendanceClaimResult,
  EssayQuestionResponse,
  EssaySubmissionResponse,
  PendingAttendanceClaimsResponse,
  PendingEssayResponse,
  RegisteredUser,
  ReviewResponse,
  WelfareRegionsResponse,
  WelfareSummaryResponse,
  VoteSubmissionResponse,
} from "@/types";

const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

const GUEST_TOKEN_KEY = "partai_guest_token";
const GUEST_REQUEST_ID_KEY = "partai_guest_request_id";
let guestTokenPromise: Promise<string> | null = null;

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

export async function fetchWithRetry(
  url: string,
  options: RequestInit,
  retries = 2,
  delayMs = 1500,
): Promise<Response> {
  for (let attempt = 0; ; attempt++) {
    try {
      return await fetch(url, options);
    } catch (error) {
      if (!(error instanceof TypeError) || attempt >= retries) {
        throw error;
      }

      await new Promise((resolve) => setTimeout(resolve, delayMs));
    }
  }
}

async function ensureSuccessfulResponse(response: Response): Promise<void> {
  if (!response.ok) {
    const detail = await response.text();
    throw new Error(
      `Permintaan API gagal (${response.status})${detail ? `: ${detail}` : ""}`,
    );
  }
}

export async function getWelfareSummary(): Promise<WelfareSummaryResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/welfare/summary`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getWelfareRegions(): Promise<WelfareRegionsResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/welfare/regions`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getCurrentUser(): Promise<RegisteredUser> {
  const response = await fetchWithRetry(`${API_BASE_URL}/user`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getReviewItems(): Promise<ReviewResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/review`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitAnswer(
  itemId: string,
  correct: boolean,
): Promise<AnswerResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/answer`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      item_id: itemId,
      correct,
      request_id: crypto.randomUUID(),
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitEssay(
  itemId: string,
  answerText: string,
  requestId: string,
): Promise<EssaySubmissionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/submit`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      item_id: itemId,
      answer_text: answerText,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getEssayQuestion(): Promise<EssayQuestionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/question`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getPendingEssay(): Promise<PendingEssayResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/review`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitEssayReview(
  submissionId: number,
  score: number,
  feedback: string | null,
  requestId: string,
): Promise<EssaySubmissionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/review`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      submission_id: submissionId,
      score,
      feedback,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getActiveEvents(): Promise<ActiveEventsResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/events/active`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getActiveVotes(): Promise<ActiveVotesResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/votes/active`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitVote(
  voteId: number,
  voteOptionId: number,
  requestId: string,
): Promise<VoteSubmissionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/votes/submit`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      vote_id: voteId,
      vote_option_id: voteOptionId,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getEventQrToken(eventId: string): Promise<string> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/events/${encodeURIComponent(eventId)}/qr`,
    {
      method: "GET",
      headers: await getHeaders(),
    },
  );

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitAttendanceClaim(
  eventId: string,
  token: string,
  lat: number,
  lng: number,
  requestId: string,
): Promise<AttendanceClaimResult> {
  const response = await fetchWithRetry(`${API_BASE_URL}/attendance/claim`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      event_id: eventId,
      token,
      lat,
      lng,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getPendingAttendanceClaims(
  eventId: string,
): Promise<PendingAttendanceClaimsResponse> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/attendance/pending?event_id=${encodeURIComponent(eventId)}`,
    {
      method: "GET",
      headers: await getHeaders(),
    },
  );

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitAttendanceAttestation(
  claimId: string,
  requestId: string,
): Promise<AttendanceAttestationResult> {
  const response = await fetchWithRetry(`${API_BASE_URL}/attendance/attest`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      claim_id: claimId,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

async function getHeaders(json = false): Promise<Headers> {
  const headers = new Headers({ Accept: "application/json" });
  headers.set("Authorization", `Bearer ${await getGuestToken()}`);

  if (json) {
    headers.set("Content-Type", "application/json");
  }

  return headers;
}
