<?php

namespace admin\admin_auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PackageController extends Controller
{
    public function viewpackages()
    {
        try {
            $packages = config('constants.package_display_names');
            return view('admin::admin.packages.view', compact('packages'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function toggle(Request $request, $vendor, $package)
    {
        try {
            $packagePath = base_path("vendor/{$vendor}/{$package}");

            set_time_limit(0);
            chdir(base_path());

            if (is_dir($packagePath)) {
                $command = "composer remove {$vendor}/{$package}";
                ob_start();
                passthru($command, $exitCode);
                $output = ob_get_clean();

                if ($exitCode === 0) {
                    // Remove published files
                    $this->removePublishedFiles($package);
                    Artisan::call('optimize:clear');
                    $message = "Package '{$vendor}/{$package}' uninstalled successfully.";
                } else {
                    $message = "❌Composer failed. Output:\n" . $output;
                    return response()->json([
                        'status' => 'error',
                        'message' => $message
                    ], 500);
                }
            } else {
                $command = "composer require {$vendor}/{$package}:@dev";
                ob_start();
                passthru($command, $exitCode);
                $output = ob_get_clean();
                if ($exitCode === 0) {
                    Artisan::call('optimize:clear');
                    $message = "Package '{$vendor}/{$package}' installed successfully.";
                } else {
                    $message = "❌Composer failed. Output:\n" . $output;
                    return response()->json([
                        'status' => 'error',
                        'message' => $message
                    ], 500);
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => $message]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    protected function removePublishedFiles($package)
    {
        $paths = [
            config_path("{$package}.php"),
            public_path("vendor/{$package}"),
            resource_path("views/vendor/{$package}"),
            base_path("routes/{$package}.php"),
        ];

        // Delete matching migration files
        $migrationFiles = glob(database_path("migrations/*{$package}*.php"));
        $paths = array_merge($paths, $migrationFiles);

        foreach ($paths as $path) {
            if (file_exists($path)) {
                is_dir($path) ? \File::deleteDirectory($path) : \File::delete($path);
            }
        }
    }

}
