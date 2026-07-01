import { ref } from "vue";
import axios from "axios";

// Must match the server-side validation in ImageUploadController.
const ALLOWED_TYPES = ["image/jpeg", "image/png", "image/webp", "image/gif"];
const MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2 MB

export function useImageUpload() {
  const uploading = ref(false);
  const error = ref(null);

  const validate = (file) => {
    if (!ALLOWED_TYPES.includes(file.type)) {
      return "fields.validation.image_invalid_type";
    }
    if (file.size > MAX_SIZE_BYTES) {
      return "fields.validation.image_too_large";
    }
    return null;
  };

  const upload = async (file) => {
    error.value = validate(file);
    if (error.value) return null;

    uploading.value = true;
    try {
      const formData = new FormData();
      formData.append("image", file);
      const { data } = await axios.post("/uploads/image", formData);
      return data.url;
    } catch (e) {
      error.value =
        e.response?.data?.errors?.image?.[0] || "fields.validation.image_upload_failed";
      return null;
    } finally {
      uploading.value = false;
    }
  };

  return { upload, uploading, error, MAX_SIZE_BYTES, ALLOWED_TYPES };
}
