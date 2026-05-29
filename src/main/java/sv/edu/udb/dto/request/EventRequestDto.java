package sv.edu.udb.dto.request;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class EventRequestDto {

    @NotBlank(message = "El título del evento es obligatorio")
    private String title;

    @NotBlank(message = "La descripción del evento es obligatoria")
    private String description;

    @NotNull(message = "La fecha del evento es obligatoria")

    private LocalDateTime eventDate;

    @NotBlank(message = "El lugar del evento es obligatorio")
    private String venue;

    @NotNull(message = "La capacidad del evento es obligatoria")
    @Positive(message = "La capacidad debe ser un número positivo")
    private Integer capacity;

    @NotNull(message = "El precio por ticket es obligatorio")
    @Positive(message = "El precio por ticket debe ser un número positivo")
    private BigDecimal pricePerTicket;
}
