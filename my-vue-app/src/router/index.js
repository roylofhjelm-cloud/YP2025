import { createRouter, createWebHistory } from "vue-router";

// Pages
import HomePage from "../pages/home-page.vue";
import ExercisePage from "../pages/exercise-page.vue";
import Login from "../pages/login.vue";
import Admin from "../pages/admin.vue";
import StudentLogin from "../pages/StudentLogin.vue";
import AccountPage from "../pages/account.vue";

const routes = [
  // Default → Login
  {
    path: "/",
    redirect: "/login",
  },

  // Admin login
  {
    path: "/login",
    name: "login",
    component: Login,
  },

  // Student login
  {
    path: "/login-student",
    name: "studentLogin",
    component: StudentLogin,
  },

  // Admin panel
  {
    path: "/admin",
    name: "admin",
    component: Admin,
  },

  // Student home
  {
    path: "/home",
    name: "home",
    component: HomePage,
  },

  // Add exercise (admin)
  {
    path: "/add",
    name: "addExercise",
    component: () => import("../pages/add-exercise.vue"),
  },
  {
    path: "/materials",
    name: "materials",
    component: () => import("../pages/materials.vue"),
  },
  {
    path: "/add-material",
    name: "addMaterial",
    component: () => import("../pages/add-material.vue"),
  },

  // Account
  {
    path: "/account",
    name: "account",
    component: AccountPage,
  },

  // Single exercise
  {
    path: "/exercise/:id",
    name: "exercise",
    component: ExercisePage,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const admin = localStorage.getItem("admin_id");
  const student = localStorage.getItem("student_id");

  if (
    (to.path === "/admin" || to.path === "/add" || to.path === "/add-material") &&
    !admin
  ) {
    return next("/login");
  }

  if (to.path === "/home" && !student && !admin) {
    return next("/login-student");
  }

  if (to.path === "/account" && !admin && !student) {
    return next("/login");
  }

  next();
});

export default router;
