import { API_BASE_URL, ensureSuccessfulResponse, fetchWithRetry } from "./client";

export type SocialAssistanceStatus = {
  program: "PKH" | "BPNT" | "PIP";
  status: "DITERIMA" | "TIDAK_DITERIMA" | "PROSES";
  period: string;
};

export class RightsCheckError extends Error {
  constructor(
    message: string,
    readonly status: number,
  ) {
    super(message);
    this.name = "RightsCheckError";
  }
}

export async function checkSocialAssistance(
  nik: string,
): Promise<SocialAssistanceStatus> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/rights/check/${encodeURIComponent(nik)}`,
    {
      method: "GET",
      headers: { Accept: "application/json" },
      cache: "no-store",
    },
  );

  if (response.status === 404) {
    throw new RightsCheckError(
      "NIK tidak terdaftar sebagai penerima bansos",
      response.status,
    );
  }

  if (response.status === 429) {
    throw new RightsCheckError(
      "Terlalu banyak percobaan. Silakan coba kembali dalam satu menit.",
      response.status,
    );
  }

  await ensureSuccessfulResponse(response);
  return response.json();
}
