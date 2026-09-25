allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    pluginManager.withPlugin("com.android.library") {
        val android = extensions.getByName("android")
        val current = runCatching {
            android.javaClass.getMethod("getNamespace").invoke(android) as String?
        }.getOrNull()
        if (current.isNullOrBlank()) {
            val manifest = file("src/main/AndroidManifest.xml")
            val pkg = if (manifest.exists()) {
                Regex("""package\s*=\s*"([^"]+)"""")
                    .find(manifest.readText())
                    ?.groupValues
                    ?.getOrNull(1)
            } else {
                null
            }
            android.javaClass.getMethod("setNamespace", String::class.java).invoke(
                android,
                pkg ?: "io.flutter.plugins.${project.name.replace("-", "_")}",
            )
        }
    }
    afterEvaluate {
        if (!pluginManager.hasPlugin("com.android.library")) return@afterEvaluate
        val android = extensions.getByName("android")
        runCatching {
            android.javaClass.getMethod("setCompileSdk", Int::class.javaPrimitiveType).invoke(android, 36)
        }
        runCatching {
            android.javaClass.getMethod("setCompileSdkVersion", Int::class.javaPrimitiveType).invoke(android, 36)
        }
    }
}

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}
subprojects {
    project.evaluationDependsOn(":app")
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
