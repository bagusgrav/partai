import { expect, test } from "@playwright/test";

const mockApiResponses: Record<
  string,
  { status?: number; body: Record<string, unknown> }
> = {
  "POST /api/auth/guest": {
    status: 201,
    body: { token: "guest-token" },
  },
  "GET /api/welfare/regions": {
    body: {
      regions: [
        {
          region_code: "001",
          region_name: "Wilayah Contoh",
          poverty: null,
          poverty_year: null,
          stunting: null,
          stunting_year: null,
          disaster_events: 0,
          disaster_year: null,
        },
      ],
    },
  },
  "POST /api/auth/register": {
    status: 201,
    body: { token: "member-token" },
  },
  "GET /api/user": {
    body: {
      id: "member-id",
      name: "Anggota Contoh",
      email: "anggota@example.test",
      cluster_id: "001",
      cluster_name: "Wilayah Contoh",
      role: "member",
      is_verified: false,
    },
  },
  "GET /api/review": {
    body: {
      items: [
        {
          id: "quiz-1",
          domain: "kewargaan",
          level: "dasar",
          payload: {
            type: "multiple-choice",
            question: "Apa warna langit cerah?",
            options: ["Biru", "Hijau"],
            answer: 0,
            explanation: "Langit cerah tampak biru.",
            sources: [],
          },
        },
      ],
    },
  },
  "GET /api/essay/question": {
    body: { item: null },
  },
  "POST /api/answer": {
    body: { status: "processed", data: {} },
  },
  "GET /api/votes/active": {
    body: {
      votes: [
        {
          id: 1,
          title: "Pilih warna",
          description: "Pilih warna favorit.",
          starts_at: "2026-01-01T00:00:00.000000Z",
          ends_at: "2099-01-01T00:00:00.000000Z",
          vote_options: [
            { id: 1, option_text: "Biru" },
            { id: 2, option_text: "Merah" },
          ],
        },
      ],
    },
  },
  "POST /api/votes/submit": {
    status: 201,
    body: { status: "processed", data: { submission_id: 1 } },
  },
};

test.beforeEach(async ({ page }) => {
  await page.route("http://127.0.0.1:8000/api/**", async (route) => {
    const request = route.request();
    const key = `${request.method()} ${new URL(request.url()).pathname}`;
    const response = mockApiResponses[key] ?? {
      status: 404,
      body: { message: `Mock API tidak ditemukan: ${key}` },
    };

    await route.fulfill({
      status: response.status ?? 200,
      contentType: "application/json",
      body: JSON.stringify(response.body),
    });
  });
});

test("landing page menampilkan judul dan ajakan belajar", async ({ page }) => {
  await page.goto("/");

  await expect(page.getByRole("heading", { name: /Aku Hadir/ })).toBeVisible();
  await expect(
    page.getByRole("link", { name: /Mulai Belajar/ }),
  ).toBeVisible();
});

test("pendaftaran berhasil dan mengarahkan anggota ke profil", async ({ page }) => {
  await page.goto("/daftar");

  await page.getByLabel("Nama Lengkap").fill("Anggota Contoh");
  await page.getByLabel("Email").fill("anggota@example.test");
  await page.getByLabel("Password").fill("password-rahasia");
  await page.getByLabel("Wilayah").selectOption("001");
  await page.getByRole("button", { name: "Daftar sebagai Anggota" }).click();

  await expect(page).toHaveURL(/\/profil$/);
  await expect(page.getByRole("heading", { name: "Profil Saya" })).toBeVisible();
  await expect(page.getByText("Anggota Contoh")).toBeVisible();
});

test("kuis menampilkan tombol lanjut setelah jawaban dikirim", async ({ page }) => {
  await page.goto("/belajar");

  await page.getByRole("button", { name: /Biru/ }).click();
  await page.getByRole("button", { name: "Kirim Jawaban" }).click();

  await expect(page.getByRole("button", { name: "Lanjut" })).toBeVisible();
});

test("voting menampilkan konfirmasi setelah suara dikirim", async ({ page }) => {
  await page.goto("/voting");

  await page.getByLabel("Biru").check();
  await page.getByRole("button", { name: "Kirim Suara" }).click();

  await expect(
    page.getByText("Terima kasih, suara Anda telah dicatat."),
  ).toBeVisible();
});
