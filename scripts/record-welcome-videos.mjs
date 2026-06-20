import { chromium } from "playwright";
import { spawn } from "node:child_process";
import { mkdir, readdir, rename, unlink } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");
const outDir = path.join(root, "public/videos/welcome");
const posterDir = path.join(root, "public/images/welcome");

const baseUrl = process.env.APP_URL ?? "http://localhost:8000";
const email = "learner@app-english.test";
const password = "password";

const clips = [
    {
        file: "speaking-practice",
        path: "/practice",
        waitMs: 10000,
        prepare: async (page) => {
            await page.locator("text=Speaking").first().waitFor({ timeout: 15000 }).catch(() => {});
        },
    },
    {
        file: "tracks-vocabulary",
        path: "/tracks",
        waitMs: 10000,
        prepare: async (page) => {
            await page.locator("text=Tracks").first().waitFor({ timeout: 15000 }).catch(() => {});
        },
    },
    {
        file: "level-map",
        path: "/practice",
        waitMs: 10000,
        prepare: async (page) => {
            await page.locator("[class*='level'], [class*='grid']").first().waitFor({ timeout: 15000 }).catch(() => {});
        },
    },
    {
        file: "dashboard-progress",
        path: "/dashboard",
        waitMs: 10000,
        prepare: async (page) => {
            await page.locator("main").first().waitFor({ timeout: 15000 }).catch(() => {});
        },
    },
];

/** @type {import("node:child_process").ChildProcess | null} */
let managedServer = null;
let startedManagedServer = false;

async function isServerReady() {
    try {
        const response = await fetch(`${baseUrl}/login`, { redirect: "follow" });

        return response.ok;
    } catch {
        return false;
    }
}

async function waitForServer(timeoutMs = 30000) {
    const startedAt = Date.now();

    while (Date.now() - startedAt < timeoutMs) {
        if (await isServerReady()) {
            return true;
        }

        await new Promise((resolve) => setTimeout(resolve, 500));
    }

    return false;
}

function startArtisanServe() {
    const serveUrl = new URL(baseUrl);
    const port = serveUrl.port || "8000";
    const host = serveUrl.hostname === "localhost" ? "127.0.0.1" : serveUrl.hostname;

    return spawn("php", ["artisan", "serve", `--host=${host}`, `--port=${port}`], {
        cwd: root,
        stdio: ["ignore", "pipe", "pipe"],
    });
}

async function ensureServer() {
    if (await isServerReady()) {
        console.log(`Servidor detectado en ${baseUrl}`);
        return;
    }

    if (process.env.RECORD_WELCOME_NO_SERVE === "1") {
        console.error(`\nNo se pudo conectar a ${baseUrl}.`);
        console.error("Inicia Laravel antes de grabar:\n  php artisan serve\n");
        console.error("O deja que el script lo inicie automáticamente (quita RECORD_WELCOME_NO_SERVE=1).\n");
        process.exit(1);
    }

    console.log(`No hay servidor en ${baseUrl}. Iniciando php artisan serve...`);
    managedServer = startArtisanServe();

    managedServer.on("error", (error) => {
        console.error(`No se pudo iniciar php artisan serve: ${error.message}`);
        process.exit(1);
    });

    managedServer.stderr?.on("data", (chunk) => {
        const message = String(chunk).trim();

        if (message.includes("Address already in use")) {
            console.error(`\nEl puerto de ${baseUrl} está ocupado por otro proceso.`);
            console.error("Detén ese servidor o cambia APP_URL antes de volver a grabar.\n");
            process.exit(1);
        }
    });

    const ready = await waitForServer();

    if (!ready) {
        console.error(`\nLaravel no respondió en ${baseUrl} tras 30 segundos.\n`);
        process.exit(1);
    }

    startedManagedServer = true;
    console.log(`Servidor listo en ${baseUrl}`);
}

function stopManagedServer() {
    if (managedServer && !managedServer.killed) {
        managedServer.kill("SIGTERM");
        managedServer = null;
    }
}

async function login(page) {
    await page.goto(`${baseUrl}/login`, { waitUntil: "domcontentloaded" });
    await page.waitForSelector("#email", { timeout: 30000 });
    await page.fill("#email", email);
    await page.fill("#password", password);
    await page.click('button[type="submit"]');
    await page.waitForURL((url) => !url.pathname.endsWith("/login"), { timeout: 20000 });
}

async function recordClip(clip) {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1280, height: 720 },
        deviceScaleFactor: 1,
        colorScheme: "dark",
        recordVideo: {
            dir: outDir,
            size: { width: 1280, height: 720 },
        },
    });

    const page = await context.newPage();

    await login(page);
    await page.goto(`${baseUrl}${clip.path}`, { waitUntil: "domcontentloaded" });
    await clip.prepare(page);
    await page.waitForTimeout(clip.waitMs);

    const posterPath = path.join(posterDir, `${clip.file}.jpg`);
    await page.screenshot({ path: posterPath, fullPage: false });

    const video = page.video();
    await context.close();
    await browser.close();

    if (!video) {
        throw new Error(`No video captured for ${clip.file}`);
    }

    const sourcePath = await video.path();
    const targetPath = path.join(outDir, `${clip.file}.webm`);

    try {
        await unlink(targetPath);
    } catch {
        // File may not exist yet.
    }

    await rename(sourcePath, targetPath);
    console.log(`Saved ${targetPath}`);
}

async function cleanupOrphans() {
    const entries = await readdir(outDir, { withFileTypes: true });

    for (const entry of entries) {
        if (entry.isFile() && entry.name.endsWith(".webm") && !clips.some((clip) => `${clip.file}.webm` === entry.name)) {
            await unlink(path.join(outDir, entry.name));
        }
    }
}

process.on("SIGINT", () => {
    stopManagedServer();
    process.exit(130);
});

process.on("SIGTERM", () => {
    stopManagedServer();
    process.exit(143);
});

try {
    await mkdir(outDir, { recursive: true });
    await mkdir(posterDir, { recursive: true });
    await cleanupOrphans();
    await ensureServer();

    for (const clip of clips) {
        console.log(`Recording ${clip.file}...`);
        await recordClip(clip);
    }

    console.log("Welcome showcase videos ready.");
} finally {
    if (startedManagedServer) {
        stopManagedServer();
        console.log("Servidor temporal detenido.");
    }
}
