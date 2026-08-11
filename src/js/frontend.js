import apiFetch from "@wordpress/api-fetch";
import confetti from "canvas-confetti";

function triggerConfettiForward(block) {
	if (!block) return;

	// Kachel-Position im Viewport ermitteln
	const rect = block.getBoundingClientRect();
	// Mittelpunkt der Kachel als Startpunkt für das Confetti
	const x = (rect.left + rect.width / 2) / window.innerWidth;
	const y = (rect.top + rect.height / 2) / window.innerHeight;

	const base = {
		origin: { x, y },
		startVelocity: 40,
		spread: 360,
		gravity: 0.5,
		decay: 0.86,
		scalar: 1,
		ticks: 180,
		zIndex: 9999,
		colors: [
			"#00bcd4", // Türkis (Cyan)
			"#ff4081", // Magenta-Pink
			"#ffd740", // Warmes Gelb
			"#7c4dff", // Lila
			"#4caf50", // Grün
			"#ff6f61", // Koralle
		],
	};

	confetti({ ...base, particleCount: 60 });
}

document.addEventListener("DOMContentLoaded", () => {
	let userId = localStorage.getItem("ud_rating_user_id");
	if (!userId) {
		userId = crypto.randomUUID();
		localStorage.setItem("ud_rating_user_id", userId);
	}
	// 🟢 Cookie setzen – damit PHP dieselbe ID kennt
	document.cookie = `ud_rating_user_id=${userId}; path=/; max-age=31536000; SameSite=Lax`;

	const ratingBlocks = document.querySelectorAll(".ud-rating-block");
	if (!ratingBlocks.length) return;

	ratingBlocks.forEach((block) => {
		const delay = parseInt(block.dataset.delay || "0", 10);

		if (delay > 0) {
			// Initial ausblenden, um FOUC zu vermeiden
			block.classList.add("is-delayed");
			block.style.opacity = "0";
			block.style.transform = "translateX(120%)";
			block.style.pointerEvents = "none";

			setTimeout(() => {
				block.classList.add("is-visible");
				block.style.opacity = "1";
				block.style.transform = "translateX(0)";
				block.style.pointerEvents = "auto";
			}, delay * 1000);
		} else {
			block.classList.add("is-visible");
			block.style.opacity = "1";
			block.style.transform = "translateX(0)";
			block.style.pointerEvents = "auto";
		}

		const stars = block.querySelectorAll(".ud-rating-block__stars svg");
		const thankyou = block.querySelector(".ud-rating-block__thankyou");
		const googleSection = block.querySelector(".ud-rating-block__google");
		const commentSection = block.querySelector(".ud-rating-block__comment");
		const commentInput = block.querySelector(
			".ud-rating-block__comment-input"
		);
		const commentSubmit = block.querySelector(
			".ud-rating-block__comment-submit"
		);

		const googleLink = block.dataset.googleLink;
		const commentPlaceholder =
			block.dataset.commentPlaceholder ||
			"Möchtest du noch kurz etwas dazu sagen?";
		const commentSavedText =
			block.dataset.commentSaved || "Dein Kommentar wurde gespeichert.";

		let currentRating = 0;
		let locked = false;

		// ⭐ Hover-Effekt
		stars.forEach((star, i) => {
			star.addEventListener("mouseenter", () => {
				if (locked) return;
				updateStars(i + 1);
			});
			star.addEventListener("mouseleave", () => {
				if (locked) return;
				updateStars(currentRating);
			});
		});

		// ⭐ Klick – Bewertung wählen
		stars.forEach((star, index) => {
			star.addEventListener("click", async () => {
				if (locked) return;

				currentRating = index + 1;
				updateStars(currentRating);
				thankyou.hidden = false;

				// 🔒 Direkt nach erstem Klick sperren (keine neuen Bewertungen)
				locked = true;
				stars.forEach((s) => (s.style.pointerEvents = "none"));
				block.classList.add("is-locked");

				// Alle Bewertungen werden gleich gespeichert und erhalten
				// dieselben optionalen Anschlussmöglichkeiten.
				if (commentSection) {
					commentSection.hidden = false;
					if (commentInput)
						commentInput.placeholder = commentPlaceholder;
				}
				if (googleSection && googleLink) {
					googleSection.hidden = false;
				}

				try {
					await apiFetch({
						path: "/ud-rating/v1/submit",
						method: "POST",
						data: { rating: currentRating, user_id: userId },
					});
					if (block.dataset.confetti === "1") {
						triggerConfettiForward(block);
					}
				} catch (err) {
					// Die Kommentar-Schaltfläche ermöglicht einen erneuten Speicherversuch.
				}
			});
		});

		// 💬 Kommentar absenden
		if (commentSubmit) {
			commentSubmit.addEventListener("click", async () => {
				const text = commentInput.value.trim();
				if (!currentRating) return; // ⚠️ locked entfernt!

				try {
					await apiFetch({
						path: "/ud-rating/v1/submit",
						method: "POST",
						data: {
							rating: currentRating,
							comment: text,
							user_id: userId,
						},
					});
					const confirmation = document.createElement("p");
					confirmation.style.fontWeight = "500";
					confirmation.textContent = commentSavedText;
					commentSection.replaceChildren(confirmation);
					lock(); // jetzt sperren
				} catch (err) {
					//console.error("❌ Kommentar-Upload fehlgeschlagen:", err);
				}
			});
		}

		function updateStars(rating) {
			stars.forEach((star, i) => {
				star.classList.toggle("is-filled", i < rating);
			});
		}

		function lock() {
			locked = true;
			block.dataset.locked = "true";
			stars.forEach((s) => (s.style.pointerEvents = "none"));
			block.classList.add("is-locked");
		}
	});
});
