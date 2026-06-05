<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MBR Drop Zone</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link
    href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/tw-elements/css/tw-elements.min.css" />
  <script src="https://cdn.tailwindcss.com/3.3.0"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    @keyframes sparkle {
      0%, 100% { text-shadow: 0 0 6px rgba(255, 255, 255, 0.35), 0 0 20px rgba(56, 189, 248, 0.25); }
      50% { text-shadow: 0 0 12px rgba(255, 255, 255, 0.75), 0 0 28px rgba(74, 222, 128, 0.5); }
    }

    @keyframes twinkle {
      0%, 100% { opacity: 0.2; transform: scale(0.8); }
      50% { opacity: 1; transform: scale(1.15); }
    }

    .sparkle-text {
      animation: sparkle 1.2s ease-in-out infinite;
    }

    .sparkle-dot {
      animation: twinkle 900ms ease-in-out infinite;
    }
  </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <main class="mx-auto flex min-h-screen w-full max-w-4xl flex-col items-center justify-center px-6 py-16">
    <div class="relative mb-14">
      <h1 class="sparkle-text rotate-[-7deg] text-center text-3xl font-black tracking-tight text-cyan-200 sm:text-5xl">
        manual encoding is for losers
      </h1>
      <span class="sparkle-dot absolute -left-4 -top-3 text-xl text-yellow-300">✦</span>
      <span class="sparkle-dot absolute -right-5 top-2 text-lg text-fuchsia-300" style="animation-delay: 140ms;">✦</span>
      <span class="sparkle-dot absolute left-1/2 -bottom-5 text-base text-emerald-300" style="animation-delay: 260ms;">✦</span>
    </div>

    <label id="drop-zone" for="file-input" class="group flex h-72 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-cyan-500/60 bg-slate-900/70 px-6 text-center transition hover:border-cyan-300 hover:bg-slate-900">
      <p class="mb-2 text-xl font-semibold text-cyan-100">Drop files here</p>
      <p class="text-sm text-slate-400">or click to browse (multiple CSV files, same type/prefix only)</p>
      <p id="file-name" class="mt-6 max-w-full truncate text-sm text-emerald-300"></p>
    </label>
    <input id="file-input" type="file" class="hidden" multiple>
  </main>

  <script>
    tailwind.config = {
      darkMode: "dark",
      theme: {
        fontFamily: {
          sans: ["Roboto", "sans-serif"],
          body: ["Roboto", "sans-serif"],
          mono: ["ui-monospace", "monospace"],
        },
      },
      corePlugins: {
        preflight: false,
      },
    };

    const zone = document.getElementById("drop-zone");
    const input = document.getElementById("file-input");
    const fileName = document.getElementById("file-name");
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    let isProcessing = false;

    function renderNames(files) {
      if (!files || files.length === 0) {
        fileName.textContent = "";
        return;
      }
      const names = Array.from(files).map((file) => file.name);
      fileName.textContent = names.join(", ");
    }

    function downloadBlob(blob, filename) {
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    }

    async function processFiles(fileList) {
      if (!fileList || fileList.length === 0 || isProcessing) {
        return;
      }

      isProcessing = true;
      zone.classList.add("pointer-events-none", "opacity-80");
      fileName.textContent = "Processing...";

      const formData = new FormData();
      Array.from(fileList).forEach((file) => formData.append("files[]", file));

      try {
        const response = await fetch("/mbr/process", {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": csrfToken,
          },
          body: formData,
        });

        if (!response.ok) {
          let message = "Failed to process files.";
          try {
            const errorData = await response.json();
            if (errorData && errorData.message) {
              message = errorData.message;
            }
          } catch (error) {
            // Ignore JSON parse error.
          }
          throw new Error(message);
        }

        const blob = await response.blob();
        const disposition = response.headers.get("Content-Disposition") || "";
        const match = disposition.match(/filename="?([^"]+)"?/i);
        const filename = match ? match[1] : "result.csv";

        await Swal.fire({
          icon: "success",
          title: "Success",
          text: "Processing complete. Download will start now.",
          background: "#0f172a",
          color: "#e2e8f0",
          confirmButtonColor: "#06b6d4",
        });

        downloadBlob(blob, filename);
        renderNames(fileList);
      } catch (error) {
        fileName.textContent = "";
        await Swal.fire({
          icon: "error",
          title: "Processing failed",
          text: error.message || "Failed to process files.",
          background: "#0f172a",
          color: "#e2e8f0",
          confirmButtonColor: "#ef4444",
        });
      } finally {
        isProcessing = false;
        zone.classList.remove("pointer-events-none", "opacity-80");
      }
    }

    ["dragenter", "dragover"].forEach((eventName) => {
      zone.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        zone.classList.add("border-emerald-300", "bg-slate-800");
      });
    });

    ["dragleave", "drop"].forEach((eventName) => {
      zone.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        zone.classList.remove("border-emerald-300", "bg-slate-800");
      });
    });

    zone.addEventListener("drop", (event) => {
      const droppedFiles = event.dataTransfer.files;
      input.files = droppedFiles;
      renderNames(droppedFiles);
      processFiles(droppedFiles);
    });

    input.addEventListener("change", (event) => {
      const selectedFiles = event.target.files;
      renderNames(selectedFiles);
      processFiles(selectedFiles);
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/tw-elements/js/tw-elements.umd.min.js"></script>
</body>
</html>
