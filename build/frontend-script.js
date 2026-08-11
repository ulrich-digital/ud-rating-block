/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!****************************!*\
  !*** ./src/js/frontend.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__);

function triggerConfirmationAnimation(block) {
  if (!block) return;
  block.classList.remove("is-celebrating");
  void block.offsetWidth;
  block.classList.add("is-celebrating");
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
  ratingBlocks.forEach(block => {
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
    stars.forEach((star, index) => {
      star.style.setProperty("--ud-rating-star-index", index);
    });
    const thankyou = block.querySelector(".ud-rating-block__thankyou");
    const googleSection = block.querySelector(".ud-rating-block__google");
    const commentSection = block.querySelector(".ud-rating-block__comment");
    const commentInput = block.querySelector(".ud-rating-block__comment-input");
    const commentSubmit = block.querySelector(".ud-rating-block__comment-submit");
    const googleLink = block.dataset.googleLink;
    const commentPlaceholder = block.dataset.commentPlaceholder || "Möchtest du noch kurz etwas dazu sagen?";
    const commentSavedText = block.dataset.commentSaved || "Dein Kommentar wurde gespeichert.";
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
        stars.forEach(s => s.style.pointerEvents = "none");
        block.classList.add("is-locked");

        // Alle Bewertungen werden gleich gespeichert und erhalten
        // dieselben optionalen Anschlussmöglichkeiten.
        if (commentSection) {
          commentSection.hidden = false;
          if (commentInput) commentInput.placeholder = commentPlaceholder;
        }
        if (googleSection && googleLink) {
          googleSection.hidden = false;
        }
        try {
          await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default()({
            path: "/ud-rating/v1/submit",
            method: "POST",
            data: {
              rating: currentRating,
              user_id: userId
            }
          });
          if (block.dataset.confirmationAnimation === "1") {
            triggerConfirmationAnimation(block);
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
          await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default()({
            path: "/ud-rating/v1/submit",
            method: "POST",
            data: {
              rating: currentRating,
              comment: text,
              user_id: userId
            }
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
      stars.forEach(s => s.style.pointerEvents = "none");
      block.classList.add("is-locked");
    }
  });
});
})();

/******/ })()
;
//# sourceMappingURL=frontend-script.js.map