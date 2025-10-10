<?php
namespace App\Services;

class PricingService
{
  private float $baseHourPrice = 0.0;
  private float $extraPersonHourPrice = 0.0;

  /**
   * تحديد سعر الساعة الأساسي وسعر الساعة للفرد الإضافي (اختياري).
   */
  public function setBase(float $baseHourPrice, ?float $extraPersonHourPrice = null): self
  {
    $this->baseHourPrice = $baseHourPrice;
    $this->extraPersonHourPrice = $extraPersonHourPrice ?? $baseHourPrice / 2;
    return $this;
  }

  /**
   * حساب تكلفة ساعة واحدة بناءً على عدد الأفراد وسعة القاعة الأدنى.
   */
  public function perHour(int $attendees, int $minCapacity): float
  {
    // ✅ لو أقل أو قد minCapacity → يتحاسب كأنه minCapacity
    if ($attendees <= $minCapacity) {
      $baseCount = $minCapacity;
      $extraCount = 0;
    } else {
      // ✅ لو أكتر من minCapacity → الأساسين minCapacity والباقي زيادة
      $baseCount = $minCapacity;
      $extraCount = $attendees - $minCapacity;
    }

    // تكلفة الأساسيين
    $base = $this->baseHourPrice * $baseCount;

    // تكلفة الأفراد الإضافيين
    $extraCost = $this->extraPersonHourPrice * $extraCount;

    return $base + $extraCost;
  }

  public function readPerHour(int $attendees, int $minCapacity,int $baseHourPrice,int $extraPersonHourPrice): float
  {
    // ✅ لو أقل أو قد minCapacity → يتحاسب كأنه minCapacity
    if ($attendees <= $minCapacity) {
      $baseCount = $minCapacity;
      $extraCount = 0;
    } else {
      // ✅ لو أكتر من minCapacity → الأساسين minCapacity والباقي زيادة
      $baseCount = $minCapacity;
      $extraCount = $attendees - $minCapacity;
    }

    // تكلفة الأساسيين
    $base = $baseHourPrice * $baseCount;

    // تكلفة الأفراد الإضافيين
    $extraCost = $extraPersonHourPrice * $extraCount;
    return $base + $extraCost;
  }
  /**
   * حساب التكلفة الإجمالية بناءً على عدد الساعات (أو الدقائق).
   */
  public function total(int $attendees, int $minCapacity, int $durationMinutes): float
  {
    $hours = $durationMinutes / 60;
    return $this->perHour($attendees, $minCapacity) * $hours;
  }
}
