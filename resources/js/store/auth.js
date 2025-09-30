import { ref } from "vue";
import axios from "axios";

const authUser = ref(null);

export function useAuth() {
  const fetchUser = async () => {
    try {
      const res = await axios.get("/api/user");
      authUser.value = res.data;
    } catch {
      authUser.value = null;
    }
  };

  const logout = async () => {
    await axios.post("/logout");
    authUser.value = null;
    window.location.href = "/login";
  };

  return {
    authUser,
    fetchUser,
    logout,
  };
}
