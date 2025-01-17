import "./bootstrap.ts";
import EditorInitializer from "./ckeditor5/EditorInitializer.ts";
import PasswordFieldCustomiser from "./easyadmin/PasswordFieldCustomiser.ts";
import PostCommentsCollectionCustomiser from "./easyadmin/PostCommentsCollectionCustomiser.ts";
import "../styles/admin/admin.css";

document.addEventListener("DOMContentLoaded", () => {
  // Password Fields
  const passwordFields: NodeListOf<HTMLDivElement> =
    document.querySelectorAll<HTMLDivElement>(
      "form[name=User] div.field-password"
    );

  if (passwordFields.length > 0) {
    new PasswordFieldCustomiser().customiseFieldsLayout(passwordFields);
  }

  // Ckeditor Field
  const editorPlaceholder: HTMLTextAreaElement | null =
    document.querySelector<HTMLTextAreaElement>("textarea#editor");

  if (editorPlaceholder) {
    new EditorInitializer().init(editorPlaceholder);
  }

  // Collection Field
  const postCommentsCollectionItems: NodeListOf<HTMLDivElement> =
    document.querySelectorAll<HTMLDivElement>("div.field-collection-item");

  if (postCommentsCollectionItems.length > 0) {
    new PostCommentsCollectionCustomiser().removeAllowDeleteIfUnauthorizedUser(
      postCommentsCollectionItems
    );
  }
});
