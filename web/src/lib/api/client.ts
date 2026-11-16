export const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

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

export async function ensureSuccessfulResponse(
  response: Response,
): Promise<void> {
  if (!response.ok) {
    const detail = await response.text();
    throw new Error(
      `Permintaan API gagal (${response.status})${detail ? `: ${detail}` : ""}`,
    );
  }
}
