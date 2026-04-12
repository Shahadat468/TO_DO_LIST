// Small page modal for important to-do list confirmations.
document.addEventListener("DOMContentLoaded", function () {
    var pageMessages = document.querySelectorAll(".message");

    pageMessages.forEach(function (message) {
        setTimeout(function () {
            message.classList.add("message-hide");

            setTimeout(function () {
                message.remove();
            }, 400);
        }, 4000);
    });

    var profileMenu = document.querySelector(".profile-menu");

    if (profileMenu) {
        document.addEventListener("click", function (event) {
            if (!profileMenu.contains(event.target)) {
                profileMenu.removeAttribute("open");
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                profileMenu.removeAttribute("open");
            }
        });
    }

    function showTaskModal(title, message, confirmText, onConfirm, dangerButton) {
        var oldModal = document.querySelector(".task-modal-backdrop");

        if (oldModal) {
            oldModal.remove();
        }

        var backdrop = document.createElement("div");
        backdrop.className = "task-modal-backdrop";

        var modal = document.createElement("div");
        modal.className = "task-modal";

        var icon = document.createElement("div");
        icon.className = "task-modal-icon";
        icon.textContent = "✓";

        var heading = document.createElement("h2");
        heading.textContent = title;

        var text = document.createElement("p");
        text.textContent = message;

        var actions = document.createElement("div");
        actions.className = "task-modal-actions";

        var cancelButton = document.createElement("button");
        cancelButton.type = "button";
        cancelButton.className = "button outline";
        cancelButton.textContent = "Cancel";

        var confirmButton = document.createElement("button");
        confirmButton.type = "button";
        confirmButton.className = dangerButton ? "button danger" : "button primary";
        confirmButton.textContent = confirmText;

        actions.appendChild(cancelButton);
        actions.appendChild(confirmButton);

        modal.appendChild(icon);
        modal.appendChild(heading);
        modal.appendChild(text);
        modal.appendChild(actions);
        backdrop.appendChild(modal);
        document.body.appendChild(backdrop);

        cancelButton.focus();

        function closeModal() {
            backdrop.remove();
        }

        cancelButton.addEventListener("click", closeModal);

        backdrop.addEventListener("click", function (event) {
            if (event.target === backdrop) {
                closeModal();
            }
        });

        confirmButton.addEventListener("click", function () {
            closeModal();
            onConfirm();
        });
    }

    function showTaskNotice(title, message) {
        showTaskModal(title, message, "OK", function () {}, false);
        var modal = document.querySelector(".task-modal");
        var cancelButton = modal.querySelector(".button.outline");

        if (cancelButton) {
            cancelButton.style.display = "none";
        }
    }

    var deleteTaskForms = document.querySelectorAll(".delete-task-form");

    deleteTaskForms.forEach(function (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            showTaskModal(
                "Delete This Task?",
                "This will remove the task from your checklist.",
                "Delete Task",
                function () {
                    form.submit();
                },
                true
            );
        });
    });

    var deleteAccountForm = document.querySelector(".delete-account-form");

    if (deleteAccountForm) {
        deleteAccountForm.addEventListener("submit", function (event) {
            var confirmInput = document.getElementById("confirm_text");
            event.preventDefault();

            if (!confirmInput || confirmInput.value !== "DELETE") {
                showTaskNotice("Confirmation Needed", "Please type DELETE exactly before deleting your account.");
                return;
            }

            showTaskModal(
                "Delete Your Task Account?",
                "This will permanently delete your account and every task in your checklist.",
                "Delete Account",
                function () {
                    deleteAccountForm.submit();
                },
                true
            );
        });
    }
});
