import { createRouter, createWebHistory } from "vue-router";
// App router: defines all routes and guards for student/admin access.

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

  // Admin-only pages
  if (
    (to.path === "/admin" || to.path === "/add" || to.path === "/add-material") &&
    !admin
  ) {
    return next("/login");
  }

  // Pages that require any logged-in user (student or admin)
  const needsLogin =
    to.path === "/home" ||
    to.path === "/account" ||
    to.path === "/materials" ||
    to.path.startsWith("/exercise/");

  if (needsLogin && !student && !admin) {
    return next("/login-student");
  }

  next();
});

export default router;
