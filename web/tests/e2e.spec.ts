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
  "GET /api/economic/summary": {
    body: {
      indicators: [
        {
          id: 1,
          indicator_type: "debt",
          title: "Utang Pemerintah",
          value: 39.5,
          unit: "persen PDB",
          period: "2026-11-17",
          source: "Simulasi (bukan data resmi)",
          trend: "stabil",
          updated_at: "2026-11-17T00:00:00.000000Z",
        },
        {
          id: 2,
          indicator_type: "apbn",
          title: "APBN",
          value: 3850,
          unit: "triliun rupiah",
          period: "2026-11-17",
          source: "Simulasi (bukan data resmi)",
          trend: "stabil",
          updated_at: "2026-11-17T00:00:00.000000Z",
        },
        {
          id: 3,
          indicator_type: "inflation",
          title: "Inflasi",
          value: 2.5,
          unit: "persen",
          period: "2026-11-17",
          source: "Simulasi (bukan data resmi)",
          trend: "stabil",
          updated_at: "2026-11-17T00:00:00.000000Z",
        },
        {
          id: 4,
          indicator_type: "exchange_rate",
          title: "Kurs USD/IDR",
          value: 16325.75,
          unit: "rupiah per USD",
          period: "2026-11-17",
          source: "https://open.er-api.com/v6/latest/USD",
          trend: "stabil",
          updated_at: "2026-11-17T00:00:00.000000Z",
        },
      ],
      last_updated: "2026-11-17T00:00:00.000000Z",
    },
  },
  "GET /api/economic/history/exchange_rate": {
    body: {
      type: "exchange_rate",
      data: [
        {
          id: 1,
          period: "2026-11-16",
          value: 16250,
        },
        {
          id: 2,
          period: "2026-11-17",
          value: 16325.75,
        },
      ],
    },
  },
  "GET /api/economic/history/inflation": {
    body: {
      type: "inflation",
      data: [
        {
          id: 3,
          period: "2026-11-16",
          value: 2.4,
        },
        {
          id: 4,
          period: "2026-11-17",
          value: 2.5,
        },
      ],
    },
  },
  "GET /api/insights/news": {
    body: {
      insights: [
        {
          id: 1,
          category: "berita",
          title: "Contoh berita terkini",
          content: "Ringkasan berita.",
          source_url: "https://example.test/berita",
          image_url: null,
          published_at: "2026-11-17T00:00:00.000000Z",
        },
      ],
    },
  },
  "GET /api/insights/cabinet": {
    body: {
      insights: [
        {
          id: 2,
          category: "kabinet",
          title: "Menteri Contoh",
          content: "Informasi kabinet.",
          source_url: null,
          image_url: null,
          published_at: null,
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
      can_vote: true,
      votes: [
        {
          id: 1,
          title: "Pilih warna",
          description: "Pilih warna favorit.",
          starts_at: "2026-01-01T00:00:00.000000Z",
          ends_at: "2099-01-01T00:00:00.000000Z",
          has_voted: false,
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
  "GET /api/recall/active": {
    body: {
      recalls: [
        {
          id: "recall-1",
          target_user_id: "target-user-id",
          target_name: "Anggota Dewan Contoh",
          reason: "Tidak memenuhi kontrak faksi.",
          evidence_url: "https://example.test/bukti",
          created_at: "2026-11-16T00:00:00.000000Z",
          status: "voting",
          agree_count: 1,
          reject_count: 0,
          active_member_count: 3,
          votes_required: 2,
          has_voted: false,
        },
      ],
    },
  },
  "POST /api/recall/request": {
    status: 201,
    body: {
      status: "processed",
      recall: {
        id: "recall-2",
        target_user_id: "target-user-id",
        status: "voting",
      },
    },
  },
  "POST /api/recall/vote": {
    status: 201,
    body: {
      status: "processed",
      data: { vote_id: "vote-1", recall_status: "voting" },
    },
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
    page.getByRole("link", { name: /Uji Kapasitasmu/ }),
  ).toBeVisible();
});

test("navbar tablet menggunakan menu vertikal tanpa overflow", async ({
  page,
}) => {
  for (const width of [768, 1024]) {
    await page.setViewportSize({ width, height: 768 });
    await page.goto("/");

    await expect(
      page.getByRole("button", { name: "Buka menu navigasi" }),
    ).toBeVisible();
    expect(
      await page.evaluate(
        () => document.documentElement.scrollWidth <= window.innerWidth,
      ),
    ).toBe(true);

    await page.getByRole("button", { name: "Buka menu navigasi" }).click();
    await expect(
      page.locator("#mobile-navigation").getByRole("link", { name: "Data Publik" }),
    ).toBeVisible();
  }
});

test("menu navbar mobile memuat navigasi dan kedua CTA", async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 812 });
  await page.route("http://127.0.0.1:8000/api/user", async (route) => {
    await route.fulfill({
      status: 401,
      contentType: "application/json",
      body: JSON.stringify({ message: "Unauthenticated." }),
    });
  });
  await page.goto("/");

  const menuButton = page.getByRole("button", { name: "Buka menu navigasi" });
  await expect(menuButton).toBeVisible();
  await menuButton.click();

  const mobileNavigation = page.locator("#mobile-navigation");
  await expect(mobileNavigation.getByRole("link", { name: "Data Publik" })).toBeVisible();
  await expect(page.getByRole("link", { name: "Gabung", exact: true })).toBeVisible();
  await expect(page.getByRole("link", { name: "Masuk", exact: true })).toBeVisible();
  expect(
    await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth),
  ).toBe(true);

  await page.getByRole("link", { name: "Gabung", exact: true }).click();
  await expect(page).toHaveURL(/\/daftar$/);
  await expect(page.getByRole("button", { name: "Buka menu navigasi" })).toHaveAttribute(
    "aria-expanded",
    "false",
  );
});

test("halaman frontend tidak overflow horizontal pada lebar ponsel", async ({
  page,
}) => {
  test.setTimeout(120_000);
  const paths = [
    "/",
    "/belajar",
    "/review",
    "/events",
    "/events/event-contoh/claim",
    "/events/event-contoh/attest",
    "/events/event-contoh/qr",
    "/manifesto",
    "/panduan",
    "/data",
    "/info",
    "/voting",
    "/recall",
    "/daftar",
    "/profil",
  ];

  for (const width of [375, 430]) {
    await page.setViewportSize({ width, height: 900 });
    for (const path of paths) {
      await page.goto(path, { waitUntil: "domcontentloaded" });
      expect(
        await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth),
        `${path} overflows at ${width}px`,
      ).toBe(true);
    }
  }
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

test("voting meminta konfirmasi dan menampilkan hasil setelah suara dikirim", async ({ page }) => {
  let hasVoted = false;
  let submissionPayload:
    | { vote_id: number; vote_option_id: number; request_id: string }
    | undefined;

  await page.route("http://127.0.0.1:8000/api/votes/active", async (route) => {
    await route.fulfill({
      contentType: "application/json",
      body: JSON.stringify({
        can_vote: true,
        votes: [
          {
            id: 1,
            title: "Pilih warna",
            description: "Pilih warna favorit.",
            starts_at: "2026-01-01T00:00:00.000000Z",
            ends_at: "2099-01-01T00:00:00.000000Z",
            has_voted: hasVoted,
            vote_options: [
              { id: 1, option_text: "Biru", ...(hasVoted ? { votes_count: 1 } : {}) },
              { id: 2, option_text: "Merah", ...(hasVoted ? { votes_count: 0 } : {}) },
            ],
          },
        ],
      }),
    });
  });
  await page.route("http://127.0.0.1:8000/api/votes/submit", async (route) => {
    submissionPayload = route.request().postDataJSON();
    hasVoted = true;
    await route.fulfill({
      status: 201,
      contentType: "application/json",
      body: JSON.stringify({ status: "processed", data: { submission_id: 1 } }),
    });
  });

  await page.goto("/voting");

  await page.getByLabel("Biru").check();
  await page.getByRole("button", { name: "Tinjau Pilihan" }).click();
  await expect(page.getByText("Konfirmasi pilihan Anda")).toBeVisible();
  expect(submissionPayload).toBeUndefined();

  await page.getByRole("button", { name: "Konfirmasi dan Kirim Suara" }).click();

  await expect(
    page.getByText("Anda sudah memilih. Terima kasih atas partisipasi Anda."),
  ).toBeVisible();
  await expect(page.getByText("1 suara masuk")).toBeVisible();
  await expect(page.getByText("1 suara (100%)")).toBeVisible();
  expect(submissionPayload).toMatchObject({ vote_id: 1, vote_option_id: 1 });
  expect(submissionPayload?.request_id).toMatch(
    /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i,
  );
});

test("voting menampilkan status dan hasil ketika pengguna sudah memilih", async ({ page }) => {
  await page.route("http://127.0.0.1:8000/api/votes/active", async (route) => {
    await route.fulfill({
      contentType: "application/json",
      body: JSON.stringify({
        can_vote: true,
        votes: [
          {
            id: 1,
            title: "Pilih warna",
            description: "Pilih warna favorit.",
            starts_at: "2026-01-01T00:00:00.000000Z",
            ends_at: "2099-01-01T00:00:00.000000Z",
            has_voted: true,
            vote_options: [
              { id: 1, option_text: "Biru", votes_count: 1 },
              { id: 2, option_text: "Merah", votes_count: 0 },
            ],
          },
        ],
      }),
    });
  });

  await page.goto("/voting");

  await expect(
    page.getByText("Anda sudah memilih. Terima kasih atas partisipasi Anda."),
  ).toBeVisible();
  await expect(page.getByText("1 suara (100%)")).toBeVisible();
  await expect(page.getByRole("button", { name: "Tinjau Pilihan" })).toHaveCount(0);
});

test("voting menolak akun yang bukan anggota terverifikasi", async ({ page }) => {
  await page.route("http://127.0.0.1:8000/api/votes/active", async (route) => {
    await route.fulfill({
      contentType: "application/json",
      body: JSON.stringify({
        can_vote: false,
        votes: [
          {
            id: 1,
            title: "Pilih warna",
            description: "Pilih warna favorit.",
            starts_at: "2026-01-01T00:00:00.000000Z",
            ends_at: "2099-01-01T00:00:00.000000Z",
            has_voted: false,
            vote_options: [{ id: 1, option_text: "Biru" }],
          },
        ],
      }),
    });
  });

  await page.goto("/voting");

  await expect(page.getByRole("status")).toContainText(
    "Voting hanya tersedia bagi anggota terverifikasi.",
  );
  await expect(page.getByLabel("Biru")).toBeDisabled();
  await expect(page.getByRole("button", { name: "Tinjau Pilihan" })).toBeDisabled();
});

test("tata kelola menampilkan recall aktif dan menerima pengajuan serta suara", async ({ page }) => {
  await page.goto("/recall");

  await expect(page.getByRole("heading", { name: "Anggota Dewan Contoh" })).toBeVisible();
  await page.getByLabel("ID Anggota yang Di-recall").fill("target-user-id");
  await page.getByLabel("Alasan", { exact: true }).fill("Melanggar kontrak faksi.");
  await page.getByRole("button", { name: "Ajukan Recall" }).click();

  await expect(
    page.getByText("Pengajuan recall berhasil dikirim dan sudah memasuki tahap voting."),
  ).toBeVisible();
  await page.getByRole("button", { name: "Setuju Recall" }).click();
  await expect(page.getByText("Terima kasih, suara Anda telah dicatat.")).toBeVisible();
});

test("info Indonesia menampilkan ringkasan ekonomi dan berita bersumber", async ({ page }) => {
  await page.goto("/info");

  await expect(
    page.getByRole("heading", { name: "Ekonomi & Wawasan Indonesia" }),
  ).toBeVisible();
  await expect(page.getByText("16.325,75 rupiah per USD")).toBeVisible();

  await page.getByRole("tab", { name: "Wawasan" }).click();
  await expect(page.getByRole("heading", { name: "Menteri Contoh" })).toBeVisible();
  await expect(
    page.getByRole("link", { name: "Contoh berita terkini" }),
  ).toHaveAttribute("href", "https://example.test/berita");
});
